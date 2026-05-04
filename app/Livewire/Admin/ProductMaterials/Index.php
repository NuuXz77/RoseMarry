<?php

namespace App\Livewire\Admin\ProductMaterials;

use App\Models\Products;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Resep (BOM)')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;
    public string $filterSort = 'newest';

    protected $listeners = [
        'recipe-created' => '$refresh',
        'recipe-updated' => '$refresh',
        'recipe-deleted' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterSort = 'newest';
        $this->resetPage();
    }

    public function detail($id)
    {
        return redirect()->route('product-materials.detail', ['product' => $id]);
    }

    public function deleteRecipe($productId)
    {
        $this->dispatch('confirm-delete-recipe', productId: $productId);
        $this->dispatch('open-modal', id: 'modal_delete_recipe');
    }

    public function render()
    {
        // We query Products instead of ProductMaterials to group them
        $products = Products::query()
            ->with(['category', 'division'])
            ->withCount('materials') // Count how many materials are in the recipe
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            // Only show products that have at least one material in their BOM
            // (Optional: remove this if you want to see products WITHOUT recipes too)
            ->whereHas('materials') 
            ->orderBy('created_at', $this->filterSort === 'oldest' ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.product-materials.index', [
            'products' => $products,
        ]);
    }
}
