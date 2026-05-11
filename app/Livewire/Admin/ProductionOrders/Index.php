<?php

namespace App\Livewire\Admin\ProductionOrders;

use App\Models\AppSetting;
use App\Models\Sales;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Antrian Pesanan')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public string $filterStatus = '';
    public string $filterService = '';
    public int $perPage = 20;

    public ?int $lastNotifiedOrderId = null;

    private function isSoundEnabled(): bool
    {
        $globalEnabled = (bool) AppSetting::get('sound_notifications_enabled', true);
        $productionEnabled = (bool) AppSetting::get('sound_notifications_production', true);

        return $globalEnabled && $productionEnabled;
    }

    private function soundVolume(): int
    {
        $volume = (int) AppSetting::get('sound_notification_volume', 80);
        return max(0, min(100, $volume));
    }

    private function soundTemplate(): string
    {
        return (string) AppSetting::get(
            'sound_notification_message_template',
            'Pesanan baru masuk. Silakan cek antrian.'
        );
    }

    public function mount(): void
    {
        // Set baseline to current latest pending order so first render won't trigger sound.
        // Only truly NEW orders (arriving after page load via poll) will notify.
        $latestPending = Sales::query()
            ->whereIn('status', ['paid', 'unpaid'])
            ->where('production_status', Sales::PRODUCTION_STATUS_PENDING)
            ->latest('id')
            ->value('id');

        $this->lastNotifiedOrderId = $latestPending ? (int) $latestPending : 0;
    }

    public function updatedSearch(): void
    {
        // no-op hook to keep UI responsive on debounce updates
    }

    public function updatingFilterStatus(): void
    {
        // no-op hook to keep UI responsive on filter updates
    }

    public function updatingFilterService(): void
    {
        // no-op hook to keep UI responsive on filter updates
    }

    public function resetFilters(): void
    {
        $this->filterStatus = '';
        $this->filterService = '';
    }

    public function setCooking(int $saleId): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk memproses pesanan.');
            return;
        }

        $sale = Sales::findOrFail($saleId);
        $sale->update(['production_status' => Sales::PRODUCTION_STATUS_COOKING]);

        $this->dispatch('show-toast', type: 'success', message: 'Pesanan masuk tahap proses.');
    }

    public function setDone(int $saleId): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk menyelesaikan pesanan.');
            return;
        }

        $sale = Sales::findOrFail($saleId);
        $sale->update(['production_status' => Sales::PRODUCTION_STATUS_DONE]);

        $this->dispatch('show-toast', type: 'success', message: 'Pesanan selesai masak.');
    }

    public function setDelivered(int $saleId): void
    {
        if (!auth()->user()?->can('production-orders.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk mengubah status pesanan.');
            return;
        }

        $sale = Sales::findOrFail($saleId);
        $sale->update(['production_status' => Sales::PRODUCTION_STATUS_DELIVERED]);

        if ($this->isSoundEnabled()) {
            $this->dispatch('play-order-notification', [
                'message' => 'Pesanan ' . $sale->service_identity . ' sudah diantar.',
                'volume' => $this->soundVolume(),
            ]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Pesanan ditandai sudah diantar.');
    }

    public function setCompleted(int $saleId): void
    {
        $sale = Sales::findOrFail($saleId);

        if ($sale->production_status !== Sales::PRODUCTION_STATUS_DELIVERED) {
            $this->dispatch('show-toast', type: 'warning', message: 'Pesanan belum diantar.');
            return;
        }

        $sale->update(['production_status' => Sales::PRODUCTION_STATUS_COMPLETED]);
        $this->dispatch('show-toast', type: 'success', message: 'Pesanan dikonfirmasi selesai.');
    }

    public function render()
    {
        $baseQuery = Sales::query()
            ->with(['cashier', 'customer'])
            ->whereIn('status', ['paid', 'unpaid']);

        $orders = (clone $baseQuery)
            ->when($this->filterStatus !== '', fn($query) => $query->where('production_status', $this->filterStatus))
            ->when($this->filterService !== '', fn($query) => $query->where('status_order', $this->filterService))
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhere('queue_number', 'like', '%' . $this->search . '%')
                        ->orWhere('guest_name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByRaw("FIELD(production_status, 'pending', 'cooking', 'done', 'delivered', 'completed')")
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $countPending = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_PENDING)->count();
        $countCooking = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_COOKING)->count();
        $countDone = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_DONE)->count();
        $countDelivered = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_DELIVERED)->count();
        $countCompleted = (clone $baseQuery)->where('production_status', Sales::PRODUCTION_STATUS_COMPLETED)->count();

        $latestPending = (clone $baseQuery)
            ->where('production_status', Sales::PRODUCTION_STATUS_PENDING)
            ->latest('id')
            ->first();

        if ($latestPending && $latestPending->id !== $this->lastNotifiedOrderId) {
            $this->lastNotifiedOrderId = (int) $latestPending->id;

            if ($this->isSoundEnabled()) {
                $template = trim($this->soundTemplate());
                $prefix = $template !== '' ? $template : 'Pesanan baru masuk. Silakan cek antrian.';
                $this->dispatch('play-order-notification', [
                    'message' => $prefix . ' ' . $latestPending->service_identity,
                    'volume' => $this->soundVolume(),
                ]);
            }
        }

        return view('livewire.admin.production-orders.index', [
            'orders' => $orders,
            'countPending' => $countPending,
            'countCooking' => $countCooking,
            'countDone' => $countDone,
            'countDelivered' => $countDelivered,
            'countCompleted' => $countCompleted,
            'canManage' => auth()->user()?->can('production-orders.manage') ?? false,
            'soundEnabled' => $this->isSoundEnabled(),
            'soundVolume' => $this->soundVolume(),
        ]);
    }
}
