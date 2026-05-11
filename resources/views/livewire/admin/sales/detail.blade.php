@use('Illuminate\Support\Facades\Storage')
<div class="space-y-6 pb-10">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('sales.index') }}" wire:navigate class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <h1 class="text-2xl font-black">Detail Penjualan</h1>
                <p class="text-sm text-base-content/50">{{ $sale->invoice_number }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($sale->production_status === 'delivered')
                <button class="btn btn-success btn-sm gap-2 print:hidden" wire:click="confirmCompleted"
                        wire:confirm="Yakin pesanan ini sudah selesai dan diterima pelanggan?">
                    <x-heroicon-o-check-badge class="w-4 h-4" />
                    Pesanan Selesai
                </button>
            @endif
            <button onclick="window.print()" class="btn btn-primary btn-sm gap-2 print:hidden">
                <x-heroicon-o-printer class="w-4 h-4" />
                Cetak
            </button>
        </div>
    </div>

    {{-- Top Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                        <x-heroicon-s-banknotes class="w-5 h-5 text-primary" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Total</p>
                        <p class="text-lg font-black text-primary truncate">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dibayar --}}
        @if ($sale->status !== 'unpaid')
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-success" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Dibayar</p>
                            <p class="text-lg font-black text-success truncate">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kembalian --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center shrink-0">
                            <x-heroicon-s-arrow-path class="w-5 h-5 text-info" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Kembalian</p>
                            <p class="text-lg font-black truncate">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Status Pembayaran --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $sale->status === 'paid' ? 'bg-success/10' : ($sale->status === 'unpaid' ? 'bg-warning/10' : 'bg-error/10') }} flex items-center justify-center shrink-0">
                        @if($sale->status === 'paid')
                            <x-heroicon-s-check-badge class="w-5 h-5 text-success" />
                        @elseif($sale->status === 'unpaid')
                            <x-heroicon-s-clock class="w-5 h-5 text-warning" />
                        @else
                            <x-heroicon-s-x-circle class="w-5 h-5 text-error" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Status</p>
                        @if($sale->status === 'paid')
                            <span class="badge badge-success badge-sm font-bold">Lunas</span>
                        @elseif($sale->status === 'unpaid')
                            <span class="badge badge-warning badge-sm font-bold">Hutang</span>
                        @else
                            <span class="badge badge-error badge-sm font-bold">Dibatalkan</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Production Status --}}
        <div class="card bg-base-100 border border-base-300">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    @php
                        $prodConfig = match($sale->production_status) {
                            'cooking'   => ['bg' => 'bg-info/10', 'text' => 'text-info', 'badge' => 'badge-info'],
                            'done'      => ['bg' => 'bg-info/10', 'text' => 'text-info', 'badge' => 'badge-info'],
                            'delivered' => ['bg' => 'bg-primary/10', 'text' => 'text-primary', 'badge' => 'badge-primary'],
                            'completed' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'badge' => 'badge-success'],
                            default     => ['bg' => 'bg-warning/10', 'text' => 'text-warning', 'badge' => 'badge-warning'],
                        };
                    @endphp
                    <div class="w-10 h-10 rounded-xl {{ $prodConfig['bg'] }} flex items-center justify-center shrink-0">
                        @if($sale->production_status === 'cooking')
                            <x-heroicon-s-fire class="w-5 h-5 {{ $prodConfig['text'] }}" />
                        @elseif($sale->production_status === 'done')
                            <x-heroicon-s-check-circle class="w-5 h-5 {{ $prodConfig['text'] }}" />
                        @elseif($sale->production_status === 'delivered')
                            <x-heroicon-s-truck class="w-5 h-5 {{ $prodConfig['text'] }}" />
                        @elseif($sale->production_status === 'completed')
                            <x-heroicon-s-check-badge class="w-5 h-5 {{ $prodConfig['text'] }}" />
                        @else
                            <x-heroicon-s-clock class="w-5 h-5 {{ $prodConfig['text'] }}" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold">Production</p>
                        <span class="badge {{ $prodConfig['badge'] }} badge-sm font-bold">{{ $sale->production_status_label }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== LEFT: Info & Items (2 cols) ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info Transaksi --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                            Informasi Transaksi
                        </h2>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-6">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Invoice</p>
                            <p class="font-bold text-sm">{{ $sale->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Tanggal</p>
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
                        @if ($sale->status !== 'unpaid')
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Metode Bayar</p>
                            <span class="badge badge-sm badge-outline font-semibold uppercase">{{ $sale->payment_method }}</span>
                        </div>
                        @endif
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-base-content/40 font-semibold mb-0.5">Identitas</p>
                            <p class="font-medium text-sm">{{ $sale->service_identity }}</p>
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
                        @if ($sale->status !== 'unpaid')
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
                        @endif
                    </div>
                </div>
            </div>

            {{-- Struk Thermal (Cetak) --}}
            <div class="card bg-base-100 border border-base-300 print:hidden">
                <div class="card-body p-0">
                    <div class="px-6 py-4 border-b border-base-200">
                        <h2 class="font-bold flex items-center gap-2">
                            <x-heroicon-o-printer class="w-5 h-5 text-primary" />
                            Struk Thermal
                        </h2>
                    </div>
                    <div class="p-6 flex flex-col items-center">
                        <div id="thermal-receipt" class="bg-white text-black p-4 sm:p-5 w-full max-w-[350px] font-mono text-[12px] border shadow-inner rounded">

                            {{-- Header --}}
                            <div class="text-center border-b border-dashed border-black pb-3 mb-3">
                                @if($appLogo)
                                    <img src="{{ asset($appLogo) }}" alt="{{ $appName }}"
                                        class="h-10 mx-auto mb-1.5 object-contain" />
                                @endif
                                <h2 class="text-base font-bold uppercase tracking-wide">{{ $appName }}</h2>
                                @if($appAddress)
                                    <p class="text-[10px] mt-1 leading-tight">{{ $appAddress }}</p>
                                @endif
                                @if($appTagline)
                                    <p class="text-[10px] mt-1 opacity-60">{{ $appTagline }}</p>
                                @endif
                            </div>

                            {{-- Invoice Info --}}
                            <div class="space-y-1 mb-3 text-[11px]">
                                <div class="flex justify-between gap-3">
                                    <span>Invoice:</span>
                                    <span class="text-right">{{ $sale->invoice_number }}</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span>Waktu:</span>
                                    <span class="text-right">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span>Kasir:</span>
                                    <span class="text-right">{{ $sale->cashier?->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span>Layanan:</span>
                                    <span class="text-right">{{ $sale->status_order ?? 'Take away' }}</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span>Pelanggan:</span>
                                    <span class="text-right">{{ $sale->service_identity }}</span>
                                </div>
                                @if ($sale->status_order === 'Dine in' && $sale->table_number)
                                    <div class="flex justify-between gap-3">
                                        <span>Meja:</span>
                                        <span class="text-right">{{ $sale->table_number }}</span>
                                    </div>
                                @endif
                                @if ($sale->catatan)
                                    <div class="flex justify-between gap-3">
                                        <span>Catatan:</span>
                                        <span class="text-right">{{ $sale->catatan }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-dashed border-black mb-3"></div>

                            {{-- Item List --}}
                            <div class="overflow-x-auto -mx-1 sm:mx-0">
                                <table class="w-full text-[11px] mb-3">
                                    <thead>
                                        <tr class="text-[10px] uppercase tracking-wide border-b border-dashed border-black">
                                            <th class="text-left pb-1.5 font-bold">Produk</th>
                                            <th class="text-center pb-1.5 font-bold w-10">Qty</th>
                                            <th class="text-right pb-1.5 font-bold w-20">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sale->items as $item)
                                            <tr>
                                                <td class="py-1 pr-2 uppercase" style="word-break:break-word;">
                                                    {{ $item->product?->name ?? 'Produk dihapus' }}
                                                </td>
                                                <td class="py-1 text-center">{{ $item->qty }}</td>
                                                <td class="py-1 text-right font-bold whitespace-nowrap">
                                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-dashed border-black mb-3"></div>

                            {{-- Totals --}}
                            <div class="space-y-1 text-[11px]">
                                <div class="flex justify-between">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if ($sale->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span>Diskon</span>
                                        <span>− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="border-t border-dashed border-black my-2"></div>
                                <div class="flex justify-between font-bold text-[13px]">
                                    <span>TOTAL</span>
                                    <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-dashed border-black my-3"></div>

                            {{-- Payment Info --}}
                            <div class="space-y-1 text-[11px]">
                                @if ($sale->status !== 'unpaid')
                                    <div class="flex justify-between">
                                        <span>Metode</span>
                                        <span class="uppercase">{{ $sale->payment_method }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Dibayar</span>
                                        <span>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($sale->change_amount > 0)
                                        <div class="flex justify-between">
                                            <span>Kembalian</span>
                                            <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                @endif
                                <div class="flex justify-between">
                                    <span>Status</span>
                                    <span
                                        class="uppercase font-bold">{{ $sale->status === 'paid' ? 'LUNAS' : ($sale->status === 'unpaid' ? 'HUTANG' : 'BATAL') }}</span>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="border-t border-dashed border-black mt-4 pt-3 text-center">
                                <p class="text-[10px]">Terima kasih telah berbelanja di <span
                                        class="font-bold">{{ $appName }}</span></p>
                                <p class="text-[10px] mt-1">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>

                        </div>

                        <button type="button" class="btn btn-primary btn-sm gap-2 mt-4 w-full max-w-[350px]" onclick="printReceipt()">
                            <x-heroicon-o-printer class="w-4 h-4" />
                            Cetak Struk
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function printReceipt() {
            const printContent = document.getElementById('thermal-receipt').innerHTML;
            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed'; iframe.style.width = '0'; iframe.style.height = '0';
            document.body.appendChild(iframe);
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`
                <html>
                    <head>
                        <title>Print Receipt</title>
                        <style>
                            @page { size: 80mm auto; margin: 0; }
                            body { font-family: monospace; width: 80mm; padding: 10px; font-size: 12px; }
                            .text-center { text-align: center; }
                            .font-bold { font-weight: bold; }
                            .flex { display: flex; }
                            .justify-between { justify-content: space-between; }
                            .border-b { border-bottom: 1px dashed black; }
                            .my-2 { margin: 8px 0; }
                            .uppercase { text-transform: uppercase; }
                        </style>
                    </head>
                    <body>${printContent}</body>
                </html>
            `);
            doc.close();
            iframe.contentWindow.focus(); iframe.contentWindow.print();
            setTimeout(() => { document.body.removeChild(iframe); }, 500);
        }
    </script>
</div>
