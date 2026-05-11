@use('Illuminate\Support\Facades\Storage')
<div class="space-y-6 pb-10">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('production.orders.index') }}" wire:navigate class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <h1 class="text-2xl font-black">Detail Pesanan</h1>
                <p class="text-sm text-base-content/50">{{ $sale->invoice_number }}</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if($canManage && $sale->production_status === 'pending')
                <button class="btn btn-info btn-sm gap-2" wire:click="setCooking"
                        wire:confirm="Yakin ingin memproses pesanan ini?">
                    <x-heroicon-o-fire class="w-4 h-4" />
                    Proses Pesanan
                </button>
            @endif

            @if($canManage && $sale->production_status === 'cooking')
                <button class="btn btn-success btn-sm gap-2" wire:click="setDone"
                        wire:confirm="Yakin pesanan ini sudah selesai masak?">
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                    Selesai Masak
                </button>
            @endif

            @if($canManage && $sale->production_status === 'done')
                <button class="btn btn-primary btn-sm gap-2" wire:click="setDelivered"
                        wire:confirm="Yakin pesanan ini sudah diantar?">
                    <x-heroicon-o-truck class="w-4 h-4" />
                    Sudah Diantar
                </button>
            @endif

            @if($sale->production_status === 'delivered')
                <button class="btn btn-neutral btn-sm gap-2" wire:click="setCompleted"
                        wire:confirm="Yakin pesanan ini sudah selesai?">
                    <x-heroicon-o-check-badge class="w-4 h-4" />
                    Pesanan Selesai
                </button>
            @endif
        </div>
    </div>

    {{-- Status & Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Status Production --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    @php
                        $statusConfig = match($sale->production_status) {
                            'cooking'   => ['bg' => 'bg-info/10', 'text' => 'text-info', 'badge' => 'badge-info'],
                            'done'      => ['bg' => 'bg-success/10', 'text' => 'text-success', 'badge' => 'badge-success'],
                            'delivered' => ['bg' => 'bg-primary/10', 'text' => 'text-primary', 'badge' => 'badge-primary'],
                            'completed' => ['bg' => 'bg-base-200', 'text' => 'text-base-content/60', 'badge' => 'badge-neutral'],
                            default     => ['bg' => 'bg-warning/10', 'text' => 'text-warning', 'badge' => 'badge-warning'],
                        };
                    @endphp
                    <div class="w-10 h-10 rounded-xl {{ $statusConfig['bg'] }} flex items-center justify-center shrink-0">
                        @if($sale->production_status === 'cooking')
                            <x-heroicon-s-fire class="w-5 h-5 {{ $statusConfig['text'] }}" />
                        @elseif($sale->production_status === 'done')
                            <x-heroicon-s-check-circle class="w-5 h-5 {{ $statusConfig['text'] }}" />
                        @elseif($sale->production_status === 'delivered')
                            <x-heroicon-s-truck class="w-5 h-5 {{ $statusConfig['text'] }}" />
                        @elseif($sale->production_status === 'completed')
                            <x-heroicon-s-check-badge class="w-5 h-5 {{ $statusConfig['text'] }}" />
                        @else
                            <x-heroicon-s-clock class="w-5 h-5 {{ $statusConfig['text'] }}" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Status Production</p>
                        <span class="badge {{ $statusConfig['badge'] }} badge-sm font-bold">{{ $sale->production_status_label }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Layanan --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $sale->status_order === 'Take away' ? 'bg-warning/10' : 'bg-info/10' }} flex items-center justify-center shrink-0">
                        @if($sale->status_order === 'Take away')
                            <x-heroicon-s-shopping-bag class="w-5 h-5 text-warning" />
                        @else
                            <x-heroicon-s-building-storefront class="w-5 h-5 text-info" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Layanan</p>
                        <span class="badge badge-sm font-bold {{ $sale->status_order === 'Take away' ? 'badge-warning' : 'badge-info' }}">
                            {{ $sale->status_order }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Items --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                        <x-heroicon-s-shopping-cart class="w-5 h-5 text-primary" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Total Item</p>
                        <p class="text-lg font-black">{{ $sale->items->sum('qty') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Harga --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                        <x-heroicon-s-banknotes class="w-5 h-5 text-success" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Total</p>
                        <p class="text-lg font-black text-success truncate">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== LEFT: Info & Items (2 cols) ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informasi Pesanan --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                            Informasi Pesanan
                        </h2>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-6">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Invoice</p>
                            <p class="font-bold text-sm">{{ $sale->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Tanggal & Waktu</p>
                            <p class="font-medium text-sm">{{ $sale->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Kasir</p>
                            <p class="font-medium text-sm">{{ $sale->cashier?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Shift</p>
                            <p class="font-medium text-sm">{{ $sale->shift?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Metode Bayar</p>
                            <span class="badge badge-sm badge-outline font-semibold uppercase">{{ $sale->payment_method ?? '-' }}</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Status Bayar</p>
                            @if($sale->status === 'paid')
                                <span class="badge badge-success badge-sm font-bold">Lunas</span>
                            @elseif($sale->status === 'unpaid')
                                <span class="badge badge-warning badge-sm font-bold">Hutang</span>
                            @else
                                <span class="badge badge-error badge-sm font-bold">Dibatalkan</span>
                            @endif
                        </div>
                        @if($sale->table_number)
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Nomor Meja</p>
                            <p class="font-medium text-sm">{{ $sale->table_number }}</p>
                        </div>
                        @endif
                        @if($sale->catatan)
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Catatan</p>
                            <p class="font-medium text-sm text-warning">{{ $sale->catatan }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Daftar Produk --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-shopping-bag class="w-5 h-5 text-primary" />
                            Daftar Produk
                        </h2>
                        <span class="badge badge-primary badge-outline badge-sm">{{ $sale->items->sum('qty') }} item</span>
                    </div>

                    <div class="divide-y divide-base-200">
                        @foreach($sale->items as $item)
                            <div class="flex items-center gap-4 px-6 py-4">
                                {{-- Thumbnail --}}
                                <div class="avatar shrink-0">
                                    <div class="w-14 h-14 rounded-xl bg-base-200 overflow-hidden">
                                        @if($item->product?->foto_product)
                                            <img src="{{ Storage::url($item->product->foto_product) }}"
                                                alt="{{ $item->product->name }}" class="object-cover w-full h-full" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <x-heroicon-o-photo class="w-6 h-6 text-base-content/20" />
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="grow min-w-0">
                                    <p class="font-bold text-sm truncate">{{ $item->product?->name ?? 'Produk dihapus' }}</p>
                                    <p class="text-xs text-base-content/50 mt-0.5">
                                        Rp {{ number_format($item->price, 0, ',', '.') }} × {{ $item->qty }}
                                    </p>
                                </div>

                                {{-- Subtotal --}}
                                <div class="text-sm font-black text-primary shrink-0">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: Pelanggan & Ringkasan (1 col) ===== --}}
        <div class="space-y-6">

            {{-- Pelanggan --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-user class="w-5 h-5 text-primary" />
                            Pelanggan
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            $customerName = $sale->service_identity;
                            $isRegistered = (bool) $sale->customer_id;
                            $hasCustomTakeAwayIdentity = trim((string) ($sale->guest_name ?? '')) !== ''
                                || trim((string) ($sale->customer?->name ?? '')) !== '';
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200/60 border border-base-300">
                            <div class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                                <x-heroicon-s-user class="w-5 h-5 text-primary" />
                            </div>
                            <div class="grow min-w-0">
                                <p class="font-bold text-sm truncate">{{ $customerName }}</p>
                                <p class="text-[10px] text-base-content/40">
                                    {{ ($sale->status_order ?? 'Take away') === 'Take away'
                                        ? ($hasCustomTakeAwayIdentity ? 'Nama pemanggilan take away' : 'Nomor antrean take away')
                                        : ($isRegistered ? 'Pelanggan terdaftar' : 'Tamu / tidak terdaftar') }}
                                </p>
                            </div>
                            <span class="badge badge-xs {{ $isRegistered ? 'badge-soft badge-success' : 'badge-soft badge-ghost' }} shrink-0">
                                {{ $isRegistered ? 'Member' : 'Guest' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Biaya --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-receipt-percent class="w-5 h-5 text-primary" />
                            Ringkasan Biaya
                        </h2>
                    </div>
                    <div class="p-6 space-y-2 text-sm">
                        <div class="flex justify-between text-base-content/60">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                        </div>

                        @if($sale->discount_amount > 0)
                            <div class="flex justify-between text-success font-medium">
                                <span>Diskon</span>
                                <span>− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="divider my-1"></div>
                        <div class="flex justify-between font-black text-base text-primary">
                            <span>TOTAL</span>
                            <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="divider my-1"></div>
                        <div class="flex justify-between text-base-content/60">
                            <span>Dibayar</span>
                            <span class="font-bold text-base-content">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($sale->change_amount > 0)
                            <div class="flex justify-between text-success">
                                <span>Kembalian</span>
                                <span class="font-bold">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timeline Production --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-clock class="w-5 h-5 text-primary" />
                            Timeline
                        </h2>
                    </div>
                    <div class="p-6">
                        @php
                            $statusOrder = ['pending', 'cooking', 'done', 'delivered', 'completed'];
                            $currentIndex = array_search($sale->production_status, $statusOrder);

                            $steps = [
                                ['key' => 'pending',   'label' => 'Pesanan Masuk',   'icon' => 'heroicon-s-inbox-arrow-down'],
                                ['key' => 'cooking',   'label' => 'Sedang Diproses', 'icon' => 'heroicon-s-fire'],
                                ['key' => 'done',      'label' => 'Selesai Masak',   'icon' => 'heroicon-s-check-circle'],
                                ['key' => 'delivered',  'label' => 'Diantar',         'icon' => 'heroicon-s-truck'],
                                ['key' => 'completed', 'label' => 'Selesai',         'icon' => 'heroicon-s-check-badge'],
                            ];
                        @endphp

                        <ul class="timeline timeline-vertical timeline-compact">
                            @foreach($steps as $i => $step)
                                @php
                                    $stepIndex = array_search($step['key'], $statusOrder);
                                    $isActive = $stepIndex <= $currentIndex;
                                    $isLast = $i === count($steps) - 1;
                                @endphp
                                <li>
                                    @if($i > 0)
                                        <hr class="{{ $isActive ? 'bg-success' : 'bg-base-300' }}" />
                                    @endif
                                    <div class="timeline-start text-xs text-base-content/50">
                                        {{ $isActive ? '' : '—' }}
                                    </div>
                                    <div class="timeline-middle">
                                        @if($isActive)
                                            <x-heroicon-s-check-circle class="w-5 h-5 text-success" />
                                        @else
                                            <x-heroicon-o-ellipsis-horizontal-circle class="w-5 h-5 text-base-content/30" />
                                        @endif
                                    </div>
                                    <div class="timeline-end timeline-box text-sm {{ $isActive ? 'font-medium' : 'text-base-content/40' }}">
                                        {{ $step['label'] }}
                                    </div>
                                    @if(!$isLast)
                                        @php
                                            $nextStepIndex = array_search($steps[$i + 1]['key'], $statusOrder);
                                            $nextIsActive = $nextStepIndex <= $currentIndex;
                                        @endphp
                                        <hr class="{{ $nextIsActive ? 'bg-success' : 'bg-base-300' }}" />
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
