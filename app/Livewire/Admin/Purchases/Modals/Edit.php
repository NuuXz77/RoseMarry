<?php

namespace App\Livewire\Admin\Purchases\Modals;

use App\Models\Purchases;
use App\Models\Suppliers;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $purchaseId = null;
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
    public string $originalStatus = 'pending';

    protected function rules(): array
    {
        return [
            'supplier_id' => $this->is_guest ? 'nullable' : 'required|exists:suppliers,id',
            'guest_supplier' => $this->is_guest ? 'required|string|max:100' : 'nullable',
            'invoice_number' => 'required|string|max:100|unique:purchases,invoice_number,' . $this->purchaseId,
            'date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:received,pending,cancelled',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
        ];
    }

    #[On('open-edit-purchase')]
    public function loadEdit(int $id): void
    {
        $purchase = Purchases::with('items.material')->findOrFail($id);

        $this->purchaseId = $purchase->id;
        $this->supplier_id = $purchase->supplier_id;
        $this->guest_supplier = $purchase->guest_supplier;
        $this->is_guest = $purchase->supplier_id === null && $purchase->guest_supplier !== null;
        $this->invoice_number = $purchase->invoice_number;
        $this->date = optional($purchase->date)->format('Y-m-d') ?? now()->toDateString();
        $this->total_amount = (float) $purchase->total_amount;
        $this->status = $purchase->status;
        $this->originalStatus = $purchase->status;
        $this->notes = $purchase->notes;

        $this->items = [];
        foreach ($purchase->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'material_id' => $item->material_id,
                'name' => $item->material->name ?? '-',
                'qty' => (float) $item->qty,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ];
        }

        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-purchase-modal');
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

    public function update(): void
    {
        if (!auth()->user()->can('purchases.edit') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah pembelian.');
            return;
        }

        $this->validate();

        try {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $purchase = Purchases::findOrFail($this->purchaseId);
                
                $purchase->update([
                    'supplier_id' => $this->is_guest ? null : $this->supplier_id,
                    'guest_supplier' => $this->is_guest ? $this->guest_supplier : null,
                    'invoice_number' => $this->invoice_number,
                    'date' => Carbon::parse($this->date)->toDateString(),
                    'total_amount' => $this->total_amount,
                    'status' => $this->status,
                    'notes' => $this->notes,
                ]);

                // Update Items
                $purchase->items()->delete();
                foreach ($this->items as $item) {
                    $purchase->items()->create([
                        'purchase_id' => $purchase->id,
                        'material_id' => $item['material_id'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // If status CHANGED TO 'received', update stock
                    if ($this->status === 'received' && $this->originalStatus !== 'received') {
                        $stock = \App\Models\MaterialStocks::firstOrCreate(
                            ['material_id' => $item['material_id']],
                            ['qty_available' => 0]
                        );
                        $stock->qty_available += $item['qty'];
                        $stock->save();

                        \App\Models\MaterialStockLogs::create([
                            'material_id' => $item['material_id'],
                            'created_by' => auth()->id(),
                            'type' => 'in',
                            'qty' => $item['qty'],
                            'description' => "Pembelian (Inv: {$this->invoice_number})",
                        ]);

                        // Optional: Update Material Master Price
                        $material = \App\Models\Materials::find($item['material_id']);
                        $material->price = $item['price'];
                        $material->save();
                    }
                }
            });

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Purchase berhasil diperbarui.');
            $this->dispatch('purchase-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui purchase: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.purchases.modals.edit', [
            'suppliers' => Suppliers::orderBy('name')->get(),
            'materials' => \App\Models\Materials::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
