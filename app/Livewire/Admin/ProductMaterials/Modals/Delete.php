<?php

namespace App\Livewire\Admin\ProductMaterials\Modals;

use App\Models\ProductMaterials;
use App\Models\Products;
use Livewire\Component;

class Delete extends Component
{
    public $recipeId = null; // Used for single material delete
    public $productId = null; // Used for entire recipe delete
    
    public $productName = '-';
    public $materialName = '-';
    public $qty_used = null;
    public $unitName = 'Unit';
    public $isBulk = false;

    protected $listeners = [
        'confirm-delete' => 'loadDelete',
        'confirm-delete-recipe' => 'loadDeleteRecipe'
    ];

    public function loadDelete($id)
    {
        $recipe = ProductMaterials::with(['product', 'material.unit'])->findOrFail($id);

        $this->recipeId = $recipe->id;
        $this->productId = null;
        $this->productName = $recipe->product->name ?? '-';
        $this->materialName = $recipe->material->name ?? '-';
        $this->qty_used = $recipe->qty_used;
        $this->unitName = $recipe->material->unit->name ?? 'Unit';
        $this->isBulk = false;
    }

    public function loadDeleteRecipe($productId)
    {
        $product = Products::withCount('materials')->findOrFail($productId);
        
        $this->productId = $product->id;
        $this->recipeId = null;
        $this->productName = $product->name;
        $this->materialName = "Seluruh Resep (" . $product->materials_count . " bahan)";
        $this->qty_used = null;
        $this->unitName = 'Semua';
        $this->isBulk = true;
    }

    public function delete()
    {
        if ($this->isBulk && $this->productId) {
            ProductMaterials::where('product_id', $this->productId)->delete();
            $msg = 'Seluruh resep produk berhasil dihapus.';
        } elseif ($this->recipeId) {
            $recipe = ProductMaterials::findOrFail($this->recipeId);
            $recipe->delete();
            $msg = 'Bahan baku berhasil dihapus dari resep.';
        }

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: $msg);
        $this->dispatch('recipe-deleted');
        $this->dispatch('recipe-updated'); // Refresh detail page if needed

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['recipeId', 'productId', 'qty_used', 'isBulk']);
        $this->productName = '-';
        $this->materialName = '-';
        $this->unitName = 'Unit';
    }

    public function render()
    {
        return view('livewire.admin.product-materials.modals.delete');
    }
}
