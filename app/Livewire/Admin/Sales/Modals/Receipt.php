<?php

namespace App\Livewire\Admin\Sales\Modals;

use App\Models\AppSetting;
use App\Models\Sales;
use Livewire\Attributes\On;
use Livewire\Component;

class Receipt extends Component
{
    public ?Sales $sale = null;

    #[On('open-receipt-modal')]
    public function loadReceipt(int $id): void
    {
        $this->sale = Sales::with(['items.product', 'customer', 'cashier', 'shift'])->find($id);
        if ($this->sale) {
            $this->dispatch('open-modal', id: 'receipt-modal');
        }
    }

    public function render()
    {
        return view('livewire.admin.sales.modals.receipt', [
            'appName' => AppSetting::get('app_name', config('app.name', 'Rosemary')),
            'appAddress' => AppSetting::get('app_address', ''),
            'appLogo' => AppSetting::get('app_logo', 'img/logo.png'),
            'appTagline' => AppSetting::get('app_tagline', 'Terima kasih atas pembelian Anda'),
        ]);
    }
}
