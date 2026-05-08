<?php

namespace App\Livewire\Admin\Sales\Modals;

use App\Models\Products;
use App\Models\ProductStocks;
use App\Models\ProductStockLogs;
use Livewire\Component;

class PosQuickAdjustStock extends Component
{
    public ?int $selected_product_id = null;
    public int $adjustment_qty = 0;
    public string $adjustment_type = 'add';
    public string $adjustment_note = '';
    public string $searchProduct = '';

    protected $rules = [
        'selected_product_id' => 'required|exists:products,id',
        'adjustment_qty'      => 'required|integer|min:1',
        'adjustment_type'     => 'required|in:add,subtract',
        'adjustment_note'     => 'required|string|max:255',
    ];

    public function save(): void
    {
        if (!auth()->user()?->can('pos.quick-adjust-stock')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk fitur ini.');
            return;
        }

        $this->validate();

        $stock = ProductStocks::where('product_id', $this->selected_product_id)->first();

        if (!$stock) {
            // Auto-create stock record if missing
            $stock = ProductStocks::create([
                'product_id'    => $this->selected_product_id,
                'qty_available' => 0,
            ]);
        }

        $diff = $this->adjustment_qty;

        if ($this->adjustment_type === 'subtract') {
            if ($stock->qty_available < $diff) {
                $this->dispatch('show-toast', type: 'error', message: 'Stok tidak mencukupi untuk pengurangan ini.');
                return;
            }
            $stock->qty_available -= $diff;
        } else {
            $stock->qty_available += $diff;
        }

        $stock->save();

        ProductStockLogs::create([
            'product_id'  => $stock->product_id,
            'created_by'  => auth()->id(),
            'type'        => 'adjustment',
            'qty'         => ($this->adjustment_type === 'add' ? $diff : -$diff),
            'description' => '[POS] ' . $this->adjustment_note,
        ]);

        $this->dispatch('close-modal', id: 'pos-quick-adjust-stock-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Stok produk berhasil disesuaikan dari POS!');
        $this->dispatch('product-changed');

        $this->reset(['selected_product_id', 'adjustment_qty', 'adjustment_note', 'searchProduct']);
        $this->adjustment_type = 'add';
    }

    public function render()
    {
        $products = Products::with('stock')
            ->where('status', true)
            ->when($this->searchProduct, fn($q) => $q->where('name', 'like', '%' . $this->searchProduct . '%'))
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('livewire.admin.sales.modals.pos-quick-adjust-stock', [
            'products' => $products,
        ]);
    }
}
