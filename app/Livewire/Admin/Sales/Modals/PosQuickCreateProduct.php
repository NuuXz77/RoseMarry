<?php

namespace App\Livewire\Admin\Sales\Modals;

use App\Models\Categories;
use App\Models\Divisions;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class PosQuickCreateProduct extends Component
{
    use WithFileUploads;

    public string  $name     = '';
    public ?string $barcode  = null;
    public         $foto_product = null;
    public ?int    $category_id  = null;
    public ?int    $division_id  = null;
    public float   $price    = 0;
    public bool    $status   = true;

    protected $rules = [
        'name'         => 'required|string|max:255',
        'barcode'      => 'nullable|string|max:100|unique:products,barcode',
        'foto_product' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'category_id'  => 'required|exists:categories,id',
        'division_id'  => 'required|exists:divisions,id',
        'price'        => 'required|numeric|min:0',
        'status'       => 'required|boolean',
    ];

    public function save(): void
    {
        if (!auth()->user()?->can('pos.quick-add-product')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk fitur ini.');
            return;
        }

        $this->validate();

        try {
            DB::beginTransaction();

            $fotoPath = $this->foto_product
                ? $this->foto_product->store('products', 'public')
                : null;

            $product = Products::create([
                'name'         => $this->name,
                'barcode'      => $this->barcode,
                'foto_product' => $fotoPath,
                'category_id'  => $this->category_id,
                'division_id'  => $this->division_id,
                'price'        => $this->price,
                'status'       => $this->status,
            ]);

            $product->stock()->create(['qty_available' => 0]);

            DB::commit();

            $this->dispatch('close-modal', id: 'pos-quick-create-product-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produk berhasil ditambahkan dari POS!');
            $this->dispatch('product-changed');
            $this->reset();
            $this->status = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambah produk: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.sales.modals.pos-quick-create-product', [
            'categories' => Categories::where('type', 'product')->where('status', true)->get(),
            'divisions'  => Divisions::where('status', true)->get(),
        ]);
    }
}
