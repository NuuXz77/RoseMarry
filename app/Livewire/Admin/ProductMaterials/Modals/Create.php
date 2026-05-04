<?php

namespace App\Livewire\Admin\ProductMaterials\Modals;

use App\Models\Materials;
use App\Models\ProductMaterials;
use App\Models\Products;
use Livewire\Component;

class Create extends Component
{
    public $product_id = '';
    public $material_id = '';
    public $qty_used = '';

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'material_id' => 'required|exists:materials,id',
        'qty_used' => 'required|numeric|min:0.001',
    ];

    protected $messages = [
        'product_id.required' => 'Produk wajib dipilih.',
        'material_id.required' => 'Bahan baku wajib dipilih.',
        'qty_used.required' => 'Jumlah bahan wajib diisi.',
        'qty_used.numeric' => 'Jumlah bahan harus berupa angka.',
        'qty_used.min' => 'Jumlah bahan minimal 0.001.',
    ];

    public function save()
    {
        $this->validate();

        $exists = ProductMaterials::where('product_id', $this->product_id)
            ->where('material_id', $this->material_id)
            ->exists();

        if ($exists) {
            $this->addError('material_id', 'Bahan baku sudah terdaftar pada resep produk ini.');
            return;
        }

        ProductMaterials::create([
            'product_id' => $this->product_id,
            'material_id' => $this->material_id,
            'qty_used' => $this->qty_used,
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Resep produk berhasil ditambahkan.');
        $this->dispatch('recipe-created');

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['product_id', 'material_id', 'qty_used']);
        $this->resetValidation();
    }

    public function render()
    {
        $availableProducts = Products::where('status', true)->orderBy('name')->get();
        $availableMaterials = Materials::with('unit')->where('status', true)->orderBy('name')->get();

        return view('livewire.admin.product-materials.modals.create', [
            'availableProducts' => $availableProducts,
            'availableMaterials' => $availableMaterials->map(fn($m) => [
                'id' => $m->id,
                'name' => "{$m->name} (" . ($m->unit->name ?? '-') . ")",
            ]),
        ]);
    }
}
