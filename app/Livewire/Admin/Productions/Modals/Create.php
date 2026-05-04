<?php

namespace App\Livewire\Admin\Productions\Modals;

use App\Models\Productions;
use App\Models\Products;
use App\Models\Schedules;
use App\Models\Shift;
use App\Models\StudentGroups;
use Livewire\Component;

class Create extends Component
{
    // Hide-only toggle: keep legacy schedule binding code for future re-enable.
    private bool $disableScheduleBinding = true;

    public $product_id;
    public $student_group_id;
    public $shift_id;
    public $qty_produced = 0;
    public $production_date;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'student_group_id' => 'required|exists:student_groups,id',
        'shift_id' => 'required|exists:shifts,id',
        'qty_produced' => 'required|integer|min:1',
        'production_date' => 'required|date',
    ];

    public function mount(): void
    {
        $this->production_date = now()->format('Y-m-d');

        if ($this->useScheduleBinding()) {
            $this->syncScheduleFields();
        }
    }

    public function updatedProductionDate(): void
    {
        if ($this->useScheduleBinding()) {
            $this->syncScheduleFields();
        }
    }

    private function isAdminUser(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    private function isProductionUser(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Production') && !$this->isAdminUser();
    }

    private function useScheduleBinding(): bool
    {
        return !$this->disableScheduleBinding && $this->isProductionUser();
    }

    private function getProductionScheduleForDate(?string $date = null): ?Schedules
    {
        $targetDate = $date ?: $this->production_date ?: now()->toDateString();

        return Schedules::query()
            ->where('type', 'production')
            ->whereDate('date', $targetDate)
            ->where('status', true)
            ->where(function ($query) {
                $query->whereNull('absence_type')
                    ->orWhere('absence_type', Schedules::ABSENCE_NONE);
            })
            ->orderBy('shift_id')
            ->first();
    }

    private function syncScheduleFields(): void
    {
        $schedule = $this->getProductionScheduleForDate();

        if (!$schedule) {
            $this->student_group_id = null;
            $this->shift_id = null;
            return;
        }

        $this->student_group_id = $schedule->student_group_id;
        $this->shift_id = $schedule->shift_id;
    }

    public function save(): void
    {
        if ($this->useScheduleBinding()) {
            $schedule = $this->getProductionScheduleForDate();

            if (!$schedule || !$schedule->student_group_id || !$schedule->shift_id) {
                $this->dispatch('show-toast',
                    type: 'error',
                    message: 'Kamu tidak memiliki jadwal produksi aktif di tanggal tersebut. Hanya Admin yang bisa memilih shift/kelompok manual.');
                return;
            }

            $this->student_group_id = $schedule->student_group_id;
            $this->shift_id = $schedule->shift_id;
        }

        $this->validate();

        Productions::create([
            'product_id' => $this->product_id,
            'student_group_id' => $this->student_group_id,
            'shift_id' => $this->shift_id,
            'qty_produced' => $this->qty_produced,
            'production_date' => $this->production_date,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Rencana produksi berhasil dibuat.');
        $this->dispatch('production-created');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['product_id', 'student_group_id', 'shift_id', 'qty_produced']);
        $this->production_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        $isProductionLocked = $this->useScheduleBinding();

        return view('livewire.admin.productions.modals.create', [
            'products' => Products::where('status', true)->get(),
            'groups' => StudentGroups::where('status', true)->get(),
            'shifts' => Shift::where('status', true)->get()->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => "{$shift->name} (" . $shift->start_time->format('H:i') . " - " . $shift->end_time->format('H:i') . ")",
                ];
            }),
            'isProductionLocked' => $isProductionLocked,
        ]);
    }
}
