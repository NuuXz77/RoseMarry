<?php

namespace App\Livewire\Admin\Productions\Modals;

use App\Models\Productions;
use App\Models\Products;
use App\Models\Shift;
use App\Models\StudentGroups;
use Livewire\Component;

class Edit extends Component
{
    public $productionId;
    public $product_id;
    public $student_group_id;
    public $shift_id;
    public $qty_produced = 0;
    public $production_date;

    protected $listeners = ['open-edit-modal' => 'loadProduction'];

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'student_group_id' => 'required|exists:student_groups,id',
        'shift_id' => 'required|exists:shifts,id',
        'qty_produced' => 'required|integer|min:1',
        'production_date' => 'required|date',
    ];

    public function loadProduction($id): void
    {
        $production = Productions::findOrFail($id);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Data produksi yang sudah selesai tidak bisa diubah.');
            return;
        }

        $this->productionId = $production->id;
        $this->product_id = $production->product_id;
        $this->student_group_id = $production->student_group_id;
        $this->shift_id = $production->shift_id;
        $this->qty_produced = $production->qty_produced;
        $this->production_date = $production->production_date?->format('Y-m-d');
    }

    public function update(): void
    {
        $this->validate();

        $production = Productions::findOrFail($this->productionId);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Data produksi yang sudah selesai tidak bisa diubah.');
            return;
        }

        $production->update([
            'product_id' => $this->product_id,
            'student_group_id' => $this->student_group_id,
            'shift_id' => $this->shift_id,
            'qty_produced' => $this->qty_produced,
            'production_date' => $this->production_date,
        ]);

        $this->dispatch('close-edit-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data produksi diperbarui.');
        $this->dispatch('production-updated');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['productionId', 'product_id', 'student_group_id', 'shift_id', 'qty_produced', 'production_date']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.productions.modals.edit', [
            'products' => Products::where('status', true)->get(),
            'groups' => StudentGroups::where('status', true)->get(),
            'shifts' => Shift::where('status', true)->get()->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => "{$shift->name} (" . $shift->start_time->format('H:i') . " - " . $shift->end_time->format('H:i') . ")",
                ];
            }),
        ]);
    }
}
