<?php

namespace App\Livewire\Admin\ProductMaterials\Modals;

use App\Models\Materials;
use App\Models\ProductMaterials;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BulkCreate extends Component
{
    public $product_id = '';
    public array $items = [];

    public function mount($product_id = '')
    {
        if ($product_id) {
            $this->product_id = $product_id;
        }
    }

    // Temporary inputs for adding to list
    public $temp_material_id = '';
    public $temp_qty_used = '';

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'items' => 'required|array|min:1',
        'items.*.material_id' => 'required|exists:materials,id',
        'items.*.qty_used' => 'required|numeric|min:0.001',
    ];

    protected $messages = [
        'product_id.required' => 'Produk wajib dipilih.',
        'items.required' => 'Daftar bahan belum ditambahkan.',
        'items.min' => 'Minimal harus ada 1 bahan baku.',
    ];

    public function addItem()
    {
        $this->validate([
            'temp_material_id' => 'required|exists:materials,id',
            'temp_qty_used' => 'required|numeric|min:0.001',
        ], [
            'temp_material_id.required' => 'Pilih bahan baku.',
            'temp_qty_used.required' => 'Isi jumlah pemakaian.',
            'temp_qty_used.numeric' => 'Jumlah harus angka.',
            'temp_qty_used.min' => 'Jumlah minimal 0.001.',
        ]);

        // Check if material already in local list
        foreach ($this->items as $item) {
            if ($item['material_id'] == $this->temp_material_id) {
                $this->addError('temp_material_id', 'Bahan ini sudah ada dalam daftar sementara.');
                return;
            }
        }

        // Get material info
        $material = Materials::with('unit')->find($this->temp_material_id);

        $this->items[] = [
            'material_id' => $material->id,
            'name' => $material->name,
            'unit' => $material->unit->name ?? 'Unit',
            'qty_used' => $this->temp_qty_used,
        ];

        // Reset temporary inputs
        $this->temp_material_id = '';
        $this->temp_qty_used = '';
        $this->resetValidation(['temp_material_id', 'temp_qty_used']);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            foreach ($this->items as $item) {
                // Check if already exists in DB to prevent duplicates
                $exists = ProductMaterials::where('product_id', $this->product_id)
                    ->where('material_id', $item['material_id'])
                    ->exists();

                if (!$exists) {
                    ProductMaterials::create([
                        'product_id' => $this->product_id,
                        'material_id' => $item['material_id'],
                        'qty_used' => $item['qty_used'],
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('close-bulk-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Resep produk berhasil disimpan secara massal.');
            $this->dispatch('recipe-created');

            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menyimpan resep: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['product_id', 'items', 'temp_material_id', 'temp_qty_used']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.product-materials.modals.bulk-create', [
            'availableProducts' => Products::where('status', true)->orderBy('name')->get(),
            'availableMaterials' => Materials::with('unit')->where('status', true)->orderBy('name')->get(),
        ]);
    }
}
