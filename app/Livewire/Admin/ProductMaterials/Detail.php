<?php

namespace App\Livewire\Admin\ProductMaterials;

use App\Models\Products;
use App\Models\ProductMaterials;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Detail extends Component
{
    public Products $product;

    #[Title('Detail Resep Produk')]

    public function mount(Products $product)
    {
        $this->product = $product->load(['materials.unit', 'materials.category', 'category', 'division']);
    }

    public function confirmDelete($materialId)
    {
        // Get the specific ProductMaterial record ID
        $recipe = ProductMaterials::where('product_id', $this->product->id)
            ->where('material_id', $materialId)
            ->first();

        if ($recipe) {
            $this->dispatch('confirm-delete', id: $recipe->id);
            $this->dispatch('open-modal', id: 'modal_delete_recipe');
        }
    }

    public function render()
    {
        return view('livewire.admin.product-materials.detail');
    }
}
