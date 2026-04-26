@use('Illuminate\Support\Facades\Storage')
@use('App\Models\Products')
<div class="min-h-screen pb-10">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center gap-4">
        <button wire:click="cancelCheckout" class="btn btn-ghost btn-sm btn-circle">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </button>
        <div>
            <h1 class="text-2xl font-black">Checkout</h1>
            <p class="text-sm text-base-content/50">Selesaikan pembayaran untuk pesanan ini</p>
        </div>
    </div>

    @if(empty($cart))
        <div class="flex flex-col items-center justify-center py-32 opacity-40">
            <x-heroicon-o-shopping-cart class="w-20 h-20 mb-4" />
            <p class="text-lg font-semibold">Tidak ada pesanan aktif</p>
            <button wire:click="cancelCheckout" class="btn btn-primary btn-sm mt-4">Kembali ke POS</button>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ===== LEFT: Order Summary ===== --}}
            <div class="lg:col-span-2 flex flex-col gap-4">

                {{-- Items card --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-0">
                        <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between">
                            <h2 class="font-bold flex items-center gap-2">
                                <x-heroicon-o-shopping-bag class="w-5 h-5 text-primary" />
                                Daftar Pesanan
                            </h2>
                            <span class="badge badge-primary badge-outline">{{ collect($cart)->sum('qty') }} item</span>
                        </div>

                        <div class="divide-y divide-base-200">
                            @foreach($cart as $index => $item)
                                @php
                                    $prod = Products::select('id','foto_product','name')->find($item['id']);
                                @endphp
                                <div class="flex items-center gap-4 px-6 py-4" wire:key="co-item-{{ $index }}">

                                    {{-- Thumbnail --}}
                                    <div class="avatar shrink-0">
                                        <div class="w-16 h-16 rounded-2xl bg-base-200 overflow-hidden">
                                            @if($prod && $prod->foto_product)
                                                <img src="{{ Storage::url($prod->foto_product) }}"
                                                    alt="{{ $item['name'] }}" class="object-cover w-full h-full" />
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <x-heroicon-o-photo class="w-8 h-8 text-base-content/20" />
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Info --}}
                                    <div class="grow min-w-0">
                                        <div class="font-bold text-sm">{{ $item['name'] }}</div>
                                        <div class="text-xs text-base-content/50 mt-0.5">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }} × {{ $item['qty'] }}
                                        </div>
                                    </div>

                                    {{-- Subtotal --}}
                                    <div class="text-base font-black text-secondary shrink-0">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Customer card (read-only — data from confirm modal) --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-5">
                        <h2 class="font-bold mb-3 flex items-center gap-2 text-sm">
                            <x-heroicon-o-user class="w-4 h-4 text-primary" />
                            Pelanggan
                        </h2>
                        @php
                            $customerName = $customer_id
                                ? ($customers->find($customer_id)?->name ?? 'Pelanggan')
                                : ($guest_name ?: 'Guest (Umum)');
                            $takeAwayIdentityPreview = $customer_id
                                ? ($customers->find($customer_id)?->name ?? 'Pelanggan')
                                : (trim((string) $guest_name) !== '' ? $guest_name : 'Nomor antrean otomatis (saat submit)');
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200/60 border border-base-300">
                            <div class="w-8 h-8 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                                <x-heroicon-s-user class="w-4 h-4 text-primary" />
                            </div>
                            <div class="grow min-w-0">
                                <p class="font-bold text-sm truncate">{{ $customerName }}</p>
                                <p class="text-[10px] text-base-content/40">
                                    {{ $customer_id ? 'Pelanggan terdaftar' : 'Tamu / tidak terdaftar' }}
                                </p>
                            </div>
                            <span class="badge badge-xs {{ $customer_id ? 'badge-soft badge-success' : 'badge-soft badge-ghost' }} shrink-0">
                                {{ $customer_id ? 'Member' : 'Guest' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200/60 border border-base-300 mt-2">
                            <div class="w-8 h-8 rounded-full bg-accent/15 flex items-center justify-center shrink-0">
                                <x-heroicon-s-arrow-up-on-square-stack class="w-4 h-4 text-accent" />
                            </div>
                            <div class="grow min-w-0">
                                <p class="font-bold text-sm truncate">{{ $status_order }}</p>
                                <p class="text-[10px] text-base-content/40">Status Order</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200/60 border border-base-300 mt-2">
                            <div class="w-8 h-8 rounded-full bg-info/15 flex items-center justify-center shrink-0">
                                <x-heroicon-s-speaker-wave class="w-4 h-4 text-info" />
                            </div>
                            <div class="grow min-w-0">
                                <p class="font-bold text-sm truncate">
                                    @if($status_order === 'Dine in')
                                        {{ $customerName }}
                                    @else
                                        {{ $takeAwayIdentityPreview }}
                                    @endif
                                </p>
                                <p class="text-[10px] text-base-content/40">Identitas Pemanggilan</p>
                            </div>
                        </div>
                        @if($status_order === 'Dine in' && $table_number)
                            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200/60 border border-base-300 mt-2">
                                <div class="w-8 h-8 rounded-full bg-secondary/15 flex items-center justify-center shrink-0">
                                    <x-heroicon-s-table-cells class="w-4 h-4 text-secondary" />
                                </div>
                                <div class="grow min-w-0">
                                    <p class="font-bold text-sm truncate">{{ $table_number }}</p>
                                    <p class="text-[10px] text-base-content/40">Nomor Meja</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Payment method card --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-6">
                        <h2 class="font-bold mb-4 flex items-center gap-2">
                            <x-heroicon-o-credit-card class="w-5 h-5 text-primary" />
                            Metode Pembayaran
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            {{-- Cash --}}
                            <label wire:click="$set('payment_method','cash')"
                                class="flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all
                                    {{ $payment_method === 'cash' ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-base-400' }}">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $payment_method === 'cash' ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/40' }}">
                                    <x-heroicon-o-banknotes class="w-6 h-6" />
                                </div>
                                <div class="grow">
                                    <div class="font-bold text-sm">Tunai (Cash)</div>
                                    <div class="text-xs text-base-content/50">Bayar dengan uang tunai</div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0
                                    {{ $payment_method === 'cash' ? 'border-primary' : 'border-base-300' }}">
                                    @if($payment_method === 'cash')
                                        <div class="w-3 h-3 rounded-full bg-primary"></div>
                                    @endif
                                </div>
                            </label>

                            {{-- QRIS --}}
                            <label wire:click="$set('payment_method','qris')"
                                class="flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all
                                    {{ $payment_method === 'qris' ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-base-400' }}">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $payment_method === 'qris' ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/40' }}">
                                    <x-heroicon-o-qr-code class="w-6 h-6" />
                                </div>
                                <div class="grow">
                                    <div class="font-bold text-sm">QRIS / Digital</div>
                                    <div class="text-xs text-base-content/50">Scan QR untuk bayar</div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0
                                    {{ $payment_method === 'qris' ? 'border-primary' : 'border-base-300' }}">
                                    @if($payment_method === 'qris')
                                        <div class="w-3 h-3 rounded-full bg-primary"></div>
                                    @endif
                                </div>
                            </label>

                        </div>
                    </div>
                </div>

                {{-- Payment Status card --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-6">
                        <h2 class="font-bold mb-4 flex items-center gap-2">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-primary" />
                            Status Pembayaran
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            {{-- Lunas --}}
                            <label wire:click="$set('payment_status','paid')"
                                class="flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all
                                    {{ $payment_status === 'paid' ? 'border-success bg-success/5' : 'border-base-300 hover:border-base-400' }}">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $payment_status === 'paid' ? 'bg-success text-success-content' : 'bg-base-200 text-base-content/40' }}">
                                    <x-heroicon-o-check-circle class="w-6 h-6" />
                                </div>
                                <div class="grow">
                                    <div class="font-bold text-sm">Lunas</div>
                                    <div class="text-xs text-base-content/50">Pembayaran langsung lunas</div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0
                                    {{ $payment_status === 'paid' ? 'border-success' : 'border-base-300' }}">
                                    @if($payment_status === 'paid')
                                        <div class="w-3 h-3 rounded-full bg-success"></div>
                                    @endif
                                </div>
                            </label>

                            {{-- Hutang --}}
                            <label wire:click="$set('payment_status','unpaid')"
                                class="flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all
                                    {{ $payment_status === 'unpaid' ? 'border-warning bg-warning/5' : 'border-base-300 hover:border-base-400' }}">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $payment_status === 'unpaid' ? 'bg-warning text-warning-content' : 'bg-base-200 text-base-content/40' }}">
                                    <x-heroicon-o-clock class="w-6 h-6" />
                                </div>
                                <div class="grow">
                                    <div class="font-bold text-sm">Hutang</div>
                                    <div class="text-xs text-base-content/50">Belum bayar / bayar nanti</div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0
                                    {{ $payment_status === 'unpaid' ? 'border-warning' : 'border-base-300' }}">
                                    @if($payment_status === 'unpaid')
                                        <div class="w-3 h-3 rounded-full bg-warning"></div>
                                    @endif
                                </div>
                            </label>

                        </div>
                    </div>
                </div>

            </div>

            {{-- ===== RIGHT: Payment Panel ===== --}}
            <div class="flex flex-col gap-4">

                {{-- Order total breakdown --}}
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-6">
                        <h2 class="font-bold mb-4 flex items-center gap-2">
                            <x-heroicon-o-receipt-percent class="w-5 h-5 text-primary" />
                            Ringkasan Biaya
                        </h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-base-content/60">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            @if($discount_amount > 0)
                                <div class="flex justify-between text-success font-medium">
                                    <span>Diskon</span>
                                    <span>− Rp {{ number_format($discount_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="divider my-2"></div>
                            <div class="flex justify-between font-black text-base text-primary">
                                <span>TOTAL</span>
                                <span>Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cash input (only when cash & paid) --}}
                @if($payment_status === 'paid' && $payment_method === 'cash')
                    <div class="card bg-base-100 border border-base-300">
                        <div class="card-body p-6">
                            <h2 class="font-bold mb-4 flex items-center gap-2">
                                <x-heroicon-o-banknotes class="w-5 h-5 text-primary" />
                                Uang Diterima
                            </h2>

                            {{-- Input --}}
                            <label class="input input-bordered flex items-center gap-2 w-full mb-3 focus-within:input-primary">
                                <span class="text-xs font-bold text-base-content/50 shrink-0">Rp</span>
                                <input type="number" wire:model.live="paid_amount"
                                    class="grow font-black text-base text-primary bg-transparent focus:outline-none"
                                    placeholder="0" />
                            </label>

                            {{-- Quick amount chips --}}
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                @foreach([5000, 10000, 20000, 50000, 100000] as $amount)
                                    <button type="button" wire:click="setPaidAmount({{ $amount }})"
                                        class="btn btn-xs btn-ghost border border-base-300 hover:border-primary hover:text-primary font-mono rounded-full px-3 transition-all">
                                        {{ number_format($amount, 0, ',', '.') }}
                                    </button>
                                @endforeach
                                <button type="button" wire:click="setPaidAmount({{ $total_amount }})"
                                    class="btn btn-xs btn-primary rounded-full px-3">
                                    Uang Pas
                                </button>
                            </div>

                            {{-- Change display --}}
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-base-200/60 border border-base-300">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40 mb-0.5">Kembalian</p>
                                    <p class="text-lg font-black {{ $change_amount > 0 ? 'text-success' : 'text-base-content/60' }}">
                                        Rp {{ number_format($change_amount, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="w-9 h-9 rounded-full bg-base-300 flex items-center justify-center text-base-content/30">
                                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($payment_status === 'unpaid')
                    <div class="card bg-base-100 shadow-sm border border-warning/30">
                        <div class="card-body p-6 flex flex-col items-center text-center gap-3">
                            <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-warning opacity-60" />
                            <div>
                                <div class="font-bold">Transaksi Hutang</div>
                                <div class="text-sm text-base-content/50 mt-1">
                                    Pesanan akan tercatat sebagai <span class="font-bold text-warning">belum lunas</span>
                                </div>
                            </div>
                            <div class="badge badge-warning badge-lg font-black px-4 py-3 text-base">
                                Rp {{ number_format($total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card bg-base-100 border border-base-300">
                        <div class="card-body p-6 flex flex-col items-center text-center gap-3">
                            <x-heroicon-o-qr-code class="w-16 h-16 text-primary opacity-60" />
                            <div>
                                <div class="font-bold">Scan QRIS</div>
                                <div class="text-sm text-base-content/50 mt-1">
                                    Arahkan kamera ke kode QR yang tersedia di kasir
                                </div>
                            </div>
                            <div class="badge badge-primary badge-lg font-black px-4 py-3 text-base">
                                Rp {{ number_format($total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Submit button --}}
                <button wire:click="submitOrder"
                    wire:loading.attr="disabled"
                    wire:target="submitOrder"
                    class="btn btn-primary btn-block btn-lg shadow-xl gap-3 h-16 text-base">
                    <!-- Normal State Icon -->
                    <span wire:loading.remove wire:target="submitOrder">
                        <x-heroicon-o-check-circle class="w-6 h-6" />
                    </span>
                    
                    <!-- Loading State Spinner -->
                    <span wire:loading wire:target="submitOrder" class="loading loading-spinner loading-sm"></span>
                    
                    <!-- Normal State Text -->
                    <span wire:loading.remove wire:target="submitOrder">Konfirmasi & Bayar</span>
                    
                    <!-- Loading State Text -->
                    <span wire:loading wire:target="submitOrder">Memproses...</span>
                </button>

                <button wire:click="cancelCheckout" class="btn btn-ghost btn-block btn-sm text-base-content/50">
                    ← Kembali ke POS
                </button>

            </div>
        </div>
    @endif
</div>

