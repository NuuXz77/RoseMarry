<?php

namespace App\Livewire\Admin\Purchases\Modals;

use App\Models\Purchases;
use App\Models\Suppliers;
use Carbon\Carbon;
use Livewire\Component;

class Create extends Component
{
    public ?int $supplier_id = null;
    public ?string $guest_supplier = null;
    public bool $is_guest = false;
    public string $invoice_number = '';
    public string $date = '';
    public float $total_amount = 0;
    public string $status = 'pending';
    public ?string $notes = null;

    // Items management
    public array $items = [];
    public ?int $selectedMaterialId = null;
    public float $itemQty = 1;
    public float $itemPrice = 0;

    protected function rules(): array
    {
        return [
            'supplier_id' => $this->is_guest ? 'nullable' : 'required|exists:suppliers,id',
            'guest_supplier' => $this->is_guest ? 'required|string|max:100' : 'nullable',
            'invoice_number' => 'required|string|max:100|unique:purchases,invoice_number',
            'date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:received,pending,cancelled',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
        ];
    }

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->generateInvoiceNumber();
    }

    public function generateInvoiceNumber(): void
    {
        $date = $this->date ? Carbon::parse($this->date) : now();
        $dateStr = $date->format('dmY'); // Format: DDMMYYYY
        $prefix = 'RSP' . $dateStr;
        
        $lastPurchase = Purchases::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastPurchase) {
            // Ambil 3 digit terakhir dan tambah 1
            $lastNumber = substr($lastPurchase->invoice_number, -3);
            $nextNumber = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        $this->invoice_number = $prefix . $nextNumber;
    }

    public function updatedDate(): void
    {
        $this->generateInvoiceNumber();
    }

    public function updatedSelectedMaterialId($value): void
    {
        if ($value) {
            $material = \App\Models\Materials::find($value);
            $this->itemPrice = (float) ($material->price ?? 0);
        }
    }

    public function addItem(): void
    {
        $this->validate([
            'selectedMaterialId' => 'required|exists:materials,id',
            'itemQty' => 'required|numeric|min:0.01',
            'itemPrice' => 'required|numeric|min:1',
        ], [
            'itemPrice.min' => 'Harga satuan harus lebih dari 0.',
        ]);

        $material = \App\Models\Materials::find($this->selectedMaterialId);

        // Check if material already in list
        foreach ($this->items as $index => $item) {
            if ($item['material_id'] == $this->selectedMaterialId) {
                $this->items[$index]['qty'] += $this->itemQty;
                $this->items[$index]['subtotal'] = $this->items[$index]['qty'] * $this->items[$index]['price'];
                $this->calculateTotal();
                $this->resetItemForm();
                return;
            }
        }

        $this->items[] = [
            'material_id' => $material->id,
            'name' => $material->name,
            'qty' => $this->itemQty,
            'price' => $this->itemPrice,
            'subtotal' => $this->itemQty * $this->itemPrice,
        ];

        $this->calculateTotal();
        $this->resetItemForm();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    private function calculateTotal(): void
    {
        $this->total_amount = array_sum(array_column($this->items, 'subtotal'));
    }

    private function resetItemForm(): void
    {
        $this->selectedMaterialId = null;
        $this->itemQty = 1;
        $this->itemPrice = 0;
    }

    public function save(): void
    {
        if (!auth()->user()->can('purchases.create') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menambah pembelian.');
            return;
        }

        $this->validate();

        try {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $purchase = Purchases::create([
                    'supplier_id' => $this->is_guest ? null : $this->supplier_id,
                    'guest_supplier' => $this->is_guest ? $this->guest_supplier : null,
                    'invoice_number' => $this->invoice_number,
                    'date' => Carbon::parse($this->date)->toDateString(),
                    'total_amount' => $this->total_amount,
                    'status' => $this->status,
                    'notes' => $this->notes,
                    'created_by' => auth()->id(),
                ]);

                foreach ($this->items as $item) {
                    $purchase->items()->create([
                        'purchase_id' => $purchase->id,
                        'material_id' => $item['material_id'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Update Stock if received
                    if ($this->status === 'received') {
                        $stock = \App\Models\MaterialStocks::firstOrCreate(
                            ['material_id' => $item['material_id']],
                            ['qty_available' => 0]
                        );
                        $stock->qty_available += $item['qty'];
                        $stock->save();

                        // Log stock change
                        \App\Models\MaterialStockLogs::create([
                            'material_id' => $item['material_id'],
                            'created_by' => auth()->id(),
                            'type' => 'in',
                            'qty' => $item['qty'],
                            'description' => "Pembelian (Inv: {$this->invoice_number})",
                        ]);

                        // Optional: Update Material Master Price (Harga Modal)
                        $material = \App\Models\Materials::find($item['material_id']);
                        $material->price = $item['price'];
                        $material->save();
                    }
                }
            });

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Purchase berhasil ditambahkan.');
            $this->dispatch('purchase-changed');

            $this->reset(['supplier_id', 'guest_supplier', 'is_guest', 'invoice_number', 'total_amount', 'status', 'notes', 'items']);
            $this->date = now()->toDateString();
            $this->status = 'pending';
            $this->generateInvoiceNumber();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah purchase: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.purchases.modals.create', [
            'suppliers' => Suppliers::orderBy('name')->get(),
            'materials' => \App\Models\Materials::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
