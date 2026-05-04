<?php

namespace App\Livewire\Admin\ProductMaterials\Modals;

use App\Models\Materials;
use App\Models\ProductMaterials;
use App\Models\Products;
use Livewire\Component;

class Edit extends Component
{
    public $recipeId = null;
    public $product_id = '';
    public $material_id = '';
    public $qty_used = '';

    protected $listeners = ['open-edit-modal' => 'loadRecipe'];

    protected function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'material_id' => 'required|exists:materials,id',
            'qty_used' => 'required|numeric|min:0.001',
        ];
    }

    protected $messages = [
        'product_id.required' => 'Produk wajib dipilih.',
        'material_id.required' => 'Bahan baku wajib dipilih.',
        'qty_used.required' => 'Jumlah bahan wajib diisi.',
        'qty_used.numeric' => 'Jumlah bahan harus berupa angka.',
        'qty_used.min' => 'Jumlah bahan minimal 0.001.',
    ];

    public function loadRecipe($id)
    {
        $recipe = ProductMaterials::findOrFail($id);

        $this->recipeId = $recipe->id;
        $this->product_id = (string) $recipe->product_id;
        $this->material_id = (string) $recipe->material_id;
        $this->qty_used = $recipe->qty_used;
    }

    public function update()
    {
        $this->validate();

        $exists = ProductMaterials::where('product_id', $this->product_id)
            ->where('material_id', $this->material_id)
            ->where('id', '!=', $this->recipeId)
            ->exists();

        if ($exists) {
            $this->addError('material_id', 'Bahan baku sudah terdaftar pada resep produk ini.');
            return;
        }

        $recipe = ProductMaterials::findOrFail($this->recipeId);
        $recipe->update([
            'product_id' => $this->product_id,
            'material_id' => $this->material_id,
            'qty_used' => $this->qty_used,
        ]);

        $this->dispatch('close-edit-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Resep produk berhasil diperbarui.');
        $this->dispatch('recipe-updated');

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['recipeId', 'product_id', 'material_id', 'qty_used']);
        $this->resetValidation();
    }

    public function render()
    {
        $availableProducts = Products::where('status', true)->orderBy('name')->get();
        $availableMaterials = Materials::with('unit')->where('status', true)->orderBy('name')->get();

        return view('livewire.admin.product-materials.modals.edit', [
            'availableProducts' => $availableProducts,
            'availableMaterials' => $availableMaterials->map(fn($m) => [
                'id' => $m->id,
                'name' => "{$m->name} (" . ($m->unit->name ?? '-') . ")",
            ]),
        ]);
    }
}
