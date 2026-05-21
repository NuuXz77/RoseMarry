<?php

namespace App\Livewire\Custumer;

use App\Models\Categories;
use App\Models\Products;
use App\Models\ProductStocks;
use App\Models\ProductStockLogs;
use App\Models\SaleItems;
use App\Models\Sales;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Order Meja')]
class Order extends Component
{
    public string $search = '';
    public string $sortBy = 'stock_desc';
    public ?string $table_number = null;

    public ?string $successMessage = null;
    public ?string $errorMessage = null;
    public ?string $lastInvoiceNumber = null;

    private static ?bool $hasQueueNumberColumn = null;

    public function mount(): void
    {
        $tableNumber = request()->query('table') ?? request()->query('meja');
        $tableNumber = is_string($tableNumber) ? trim($tableNumber) : null;
        $this->table_number = $tableNumber !== '' ? $tableNumber : null;
    }

    private function resolveShiftId(): ?int
    {
        $now = now()->format('H:i:s');

        $activeShift = Shift::query()
            ->where('status', true)
            ->where(function ($query) use ($now) {
                $query->where(function ($subQuery) use ($now) {
                    $subQuery->whereColumn('start_time', '<=', 'end_time')
                        ->whereTime('start_time', '<=', $now)
                        ->whereTime('end_time', '>=', $now);
                })
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->whereColumn('start_time', '>', 'end_time')
                            ->where(function ($timeQuery) use ($now) {
                                $timeQuery->whereTime('start_time', '<=', $now)
                                    ->orWhereTime('end_time', '>=', $now);
                            });
                    });
            })
            ->orderBy('id')
            ->first();

        if ($activeShift) {
            return (int) $activeShift->id;
        }

        $fallbackShift = Shift::query()->where('status', true)->orderBy('id')->first();
        if ($fallbackShift) {
            return (int) $fallbackShift->id;
        }

        return Shift::query()->orderBy('id')->value('id');
    }

    private function salesHasQueueNumberColumn(): bool
    {
        if (self::$hasQueueNumberColumn === null) {
            self::$hasQueueNumberColumn = Schema::hasColumn('sales', 'queue_number');
        }

        return self::$hasQueueNumberColumn;
    }

    public function updatedSortBy(string $value): void
    {
        $allowed = ['stock_desc', 'stock_asc', 'price_desc', 'price_asc'];
        if (!in_array($value, $allowed, true)) {
            $this->sortBy = 'stock_desc';
        }
    }

    /**
     * @param array<int, array{id:mixed, qty:mixed}> $items
     */
    public function submitOrderFromClient(array $items, ?string $guestName = null): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $guestName = trim((string) $guestName);
        if ($guestName === '') {
            $this->errorMessage = 'Nama pembeli wajib diisi.';
            return;
        }

        if (empty($items)) {
            $this->errorMessage = 'Keranjang masih kosong.';
            return;
        }

        $productIds = collect($items)
            ->pluck('id')
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            $this->errorMessage = 'Keranjang tidak valid.';
            return;
        }

        $products = Products::with('stock')
            ->whereIn('id', $productIds)
            ->where('status', true)
            ->get()
            ->keyBy('id');

        $normalizedCart = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $id = isset($item['id']) && is_numeric($item['id']) ? (int) $item['id'] : 0;
            $qty = isset($item['qty']) && is_numeric($item['qty']) ? (int) $item['qty'] : 0;

            if ($id <= 0 || $qty <= 0) {
                continue;
            }

            $product = $products->get($id);
            if (!$product) {
                $this->errorMessage = 'Ada produk yang sudah tidak tersedia.';
                return;
            }

            $stockAvailable = (int) (optional($product->stock)->qty_available ?? 0);
            if ($stockAvailable <= 0) {
                $this->errorMessage = "Stok {$product->name} sudah habis.";
                return;
            }

            if ($qty > $stockAvailable) {
                $this->errorMessage = "Stok {$product->name} tidak mencukupi.";
                return;
            }

            $price = (float) $product->price;
            $rowSubtotal = $price * $qty;

            $normalizedCart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'qty' => $qty,
                'subtotal' => $rowSubtotal,
            ];

            $subtotal += $rowSubtotal;
        }

        if (empty($normalizedCart)) {
            $this->errorMessage = 'Keranjang masih kosong.';
            return;
        }

        $shiftId = $this->resolveShiftId();
        if (!$shiftId) {
            $this->errorMessage = 'Shift aktif tidak ditemukan. Hubungi kasir.';
            return;
        }

        DB::beginTransaction();
        try {
            $todayDate = now()->toDateString();
            $hasQueueNumberColumn = $this->salesHasQueueNumberColumn();

            $lastSaleToday = Sales::whereDate('created_at', $todayDate)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $nextNumber = 1;
            if ($lastSaleToday && preg_match('/RSM\d{6}(\d{3})$/', (string) $lastSaleToday->invoice_number, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }

            $invoiceNumber = 'RSM' . now()->format('ymd') . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $salePayload = [
                'invoice_number' => $invoiceNumber,
                'customer_id' => null,
                'guest_name' => $guestName,
                'status_order' => Sales::ORDER_STATUS_DINE_IN,
                'table_number' => $this->table_number ?: null,
                'shift_id' => $shiftId,
                'cashier_student_id' => null,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_method' => 'cash',
                'status' => 'unpaid',
                'production_status' => Sales::PRODUCTION_STATUS_PENDING,
            ];

            if ($hasQueueNumberColumn) {
                $salePayload['queue_number'] = null;
            }

            $sale = Sales::create($salePayload);

            foreach ($normalizedCart as $item) {
                SaleItems::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $stock = ProductStocks::where('product_id', $item['id'])->first();
                if ($stock) {
                    $stock->decrement('qty_available', $item['qty']);

                    ProductStockLogs::create([
                        'product_id' => $item['id'],
                        'type' => 'out',
                        'qty' => -$item['qty'],
                        'description' => "Pesanan meja #{$sale->invoice_number}",
                        'reference_type' => Sales::class,
                        'reference_id' => $sale->id,
                        'created_by' => null,
                    ]);
                }
            }

            DB::commit();

            $this->lastInvoiceNumber = $sale->invoice_number;
            $this->successMessage = 'Pesanan berhasil dikirim. Silakan bayar di kasir.';
            $this->dispatch('customer-order-submitted', invoice: $sale->invoice_number);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorMessage = 'Gagal mengirim pesanan. Coba lagi.';
        }
    }

    public function render()
    {
        $stockSubquery = '(SELECT COALESCE(ps.qty_available, 0) FROM product_stocks ps WHERE ps.product_id = products.id LIMIT 1)';

        $productsQuery = Products::query()
            ->with('stock', 'category')
            ->where('status', true)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));

        $productsQuery->orderByRaw('(CASE WHEN ' . $stockSubquery . ' > 0 THEN 0 ELSE 1 END) ASC');

        match ($this->sortBy) {
            'stock_asc' => $productsQuery->orderByRaw($stockSubquery . ' ASC'),
            'price_desc' => $productsQuery->orderBy('price', 'desc'),
            'price_asc' => $productsQuery->orderBy('price', 'asc'),
            default => $productsQuery->orderByRaw($stockSubquery . ' DESC'),
        };

        $products = $productsQuery
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.custumer.order', [
            'products' => $products,
            'categories' => Categories::where('type', 'product')->get(),
        ]);
    }
}
