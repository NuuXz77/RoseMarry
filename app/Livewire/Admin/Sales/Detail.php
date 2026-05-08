<?php

namespace App\Livewire\Admin\Sales;

use App\Models\AppSetting;
use App\Models\Sales;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Detail Penjualan')]
class Detail extends Component
{
    public Sales $sale;

    public function mount(Sales $sale)
    {
        $this->sale->load(['items.product', 'cashier', 'shift', 'customer']);
    }

    public function confirmCompleted(): void
    {
        if (!auth()->user()?->can('sales.edit') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin untuk mengubah status pesanan.');
            return;
        }

        if ($this->sale->production_status !== Sales::PRODUCTION_STATUS_DELIVERED) {
            $this->dispatch('show-toast', type: 'warning', message: 'Pesanan belum diantar oleh production.');
            return;
        }

        $this->sale->update(['production_status' => Sales::PRODUCTION_STATUS_COMPLETED]);
        $this->dispatch('show-toast', type: 'success', message: 'Pesanan dikonfirmasi selesai.');
    }

    public function render()
    {
        return view('livewire.admin.sales.detail', [
            'appName' => AppSetting::get('app_name', config('app.name', 'Rosemary')),
            'appAddress' => AppSetting::get('app_address', ''),
            'appLogo' => AppSetting::get('app_logo', 'img/logo.png'),
            'appTagline' => AppSetting::get('app_tagline', 'Terima kasih atas pembelian Anda'),
        ]);
    }
}
