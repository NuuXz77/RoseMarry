@use('Illuminate\Support\Facades\Storage')
<div class="min-h-screen pb-10 px-3 sm:px-4 lg:px-0 invoice-page">

    {{-- Action Bar --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div class="flex items-center gap-3 sm:gap-4">
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
            <div>
                <h1 class="text-xl sm:text-2xl font-black">Invoice</h1>
                <p class="text-sm text-base-content/50">{{ $sale->invoice_number }}</p>
            </div>
        </div>
        <div class="flex w-full sm:w-auto items-center gap-2">
            <button onclick="printInvoiceOnly()" class="btn btn-primary btn-sm gap-2 flex-1 sm:flex-none">
                <x-heroicon-o-printer class="w-4 h-4" />
                Cetak Struk
            </button>
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm gap-2 flex-1 sm:flex-none">
                <x-heroicon-o-arrow-path class="w-4 h-4" />
                Transaksi Baru
            </button>
        </div>
    </div>

    {{-- Content: 2 Column on Desktop --}}
    <div class="flex flex-col lg:flex-row gap-6 max-w-5xl mx-auto">

        {{-- LEFT: Struk / Receipt --}}
        <div class="w-full lg:w-[340px] shrink-0 mx-auto lg:mx-0 invoice-print-area">
            <div class="invoice-paper card bg-white text-black rounded-none border-0 shadow-lg lg:shadow-xl">
                <div class="invoice-body card-body p-4 sm:p-5 font-mono text-[12px]">

                    {{-- Header --}}
                    <div class="text-center border-b border-dashed border-black pb-3 mb-3">
                        @if($appLogo)
                            <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="h-10 mx-auto mb-1.5 object-contain" />
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
                        <div class="flex justify-between">
                            <span>Status</span>
                            <span class="uppercase font-bold">{{ $sale->status === 'paid' ? 'LUNAS' : ($sale->status === 'unpaid' ? 'HUTANG' : 'BATAL') }}</span>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-dashed border-black mt-4 pt-3 text-center">
                        <p class="text-[10px]">Terima kasih telah berbelanja di <span
                                class="font-bold">{{ $appName }}</span></p>
                        <p class="text-[10px] mt-1">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>

                </div>
            </div>
        </div>

        {{-- RIGHT: Order Summary (screen only) --}}
        <div class="flex-1 space-y-4 print:hidden">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-4">
                        <div class="text-[10px] uppercase tracking-wider text-base-content/40 font-bold">Total</div>
                        <div class="text-xl font-black text-primary">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-4">
                        <div class="text-[10px] uppercase tracking-wider text-base-content/40 font-bold">Status</div>
                        @if($sale->status === 'paid')
                            <span class="badge badge-success font-bold mt-1">Lunas</span>
                        @elseif($sale->status === 'unpaid')
                            <span class="badge badge-warning font-bold mt-1">Hutang</span>
                        @else
                            <span class="badge badge-error font-bold mt-1">Dibatalkan</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Items Detail --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-5 py-3 border-b border-base-200">
                        <h3 class="font-bold text-sm flex items-center gap-2">
                            <x-heroicon-o-shopping-bag class="w-4 h-4 text-primary" />
                            Detail Produk
                        </h3>
                    </div>
                    <div class="divide-y divide-base-200">
                        @foreach($sale->items as $item)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <div class="avatar shrink-0">
                                    <div class="w-10 h-10 rounded-lg bg-base-200 overflow-hidden">
                                        @if($item->product?->foto_product)
                                            <img src="{{ Storage::url($item->product->foto_product) }}" alt="{{ $item->product->name }}" class="object-cover w-full h-full" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <x-heroicon-o-photo class="w-4 h-4 text-base-content/20" />
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="grow min-w-0">
                                    <p class="font-bold text-sm truncate">{{ $item->product?->name ?? 'Produk dihapus' }}</p>
                                    <p class="text-xs text-base-content/50">{{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-sm font-black text-primary shrink-0">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Payment Detail --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-5 py-3 border-b border-base-200">
                        <h3 class="font-bold text-sm flex items-center gap-2">
                            <x-heroicon-o-banknotes class="w-4 h-4 text-primary" />
                            Pembayaran
                        </h3>
                    </div>
                    <div class="p-5 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Subtotal</span>
                            <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($sale->discount_amount > 0)
                            <div class="flex justify-between text-success">
                                <span>Diskon</span>
                                <span>− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="divider my-1"></div>
                        <div class="flex justify-between font-black text-primary">
                            <span>Total</span>
                            <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Dibayar</span>
                            <span class="font-bold">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
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

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="printInvoiceOnly()" class="btn btn-primary gap-2 flex-1">
                    <x-heroicon-o-printer class="w-5 h-5" />
                    Cetak Struk
                </button>
                <button wire:click="backToPOS" class="btn btn-ghost gap-2 flex-1">
                    <x-heroicon-o-arrow-path class="w-5 h-5" />
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>


    {{-- Styles --}}
    <style>
        .invoice-paper {
            position: relative;
            isolation: isolate;
        }

        .invoice-body {
            position: relative;
        }

        .invoice-body::before {
            content: '';
            position: absolute;
            inset: 10% 8%;
            background-image: url('{{ asset('img/label.jpeg') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: min(82%, 330px);
            opacity: 0.08;
            filter: drop-shadow(0 8px 14px rgba(0, 0, 0, 0.22));
            pointer-events: none;
            z-index: 0;
        }

        .invoice-body>* {
            position: relative;
            z-index: 1;
        }

        .invoice-print-area table {
            table-layout: auto;
        }

        .invoice-print-area table td,
        .invoice-print-area table th {
            vertical-align: top;
            word-break: break-word;
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide everything first */
            body * {
                visibility: hidden !important;
            }

            /* Show only the receipt */
            .invoice-print-area,
            .invoice-print-area * {
                visibility: visible !important;
            }

            /* Hide non-print elements completely */
            .print\:hidden {
                display: none !important;
            }

            /* Reset page container */
            html, body {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: visible !important;
            }

            .invoice-page {
                position: static !important;
                width: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            /* Kill the flex layout */
            .invoice-page > div {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-print-area {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-paper {
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                overflow: visible !important;
            }

            .invoice-body {
                padding: 3mm !important;
                overflow: visible !important;
            }

            .invoice-body::before {
                display: block !important;
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Table */
            .invoice-print-area table {
                table-layout: fixed !important;
                width: 100% !important;
            }

            .invoice-print-area table th:nth-child(1),
            .invoice-print-area table td:nth-child(1) { width: 50% !important; }

            .invoice-print-area table th:nth-child(2),
            .invoice-print-area table td:nth-child(2) { width: 12% !important; }

            .invoice-print-area table th:nth-child(3),
            .invoice-print-area table td:nth-child(3) { width: 38% !important; }

            .overflow-x-auto {
                overflow: visible !important;
            }
        }
    </style>

    <script>
        function printInvoiceOnly() {
            window.print();
        }
    </script>
</div>
