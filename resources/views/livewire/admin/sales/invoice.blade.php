@use('Illuminate\Support\Facades\Storage')
<div class="min-h-screen pb-10 px-3 sm:px-4 lg:px-0 invoice-page" x-data="{
        btStatus: 'idle',
        btError: '',
        btDeviceName: '',
        receiptData: @js([
            'appName' => $appName ?? 'TOKO',
            'appAddress' => $appAddress ?? '',
            'appTagline' => $appTagline ?? '',
            'invoiceNumber' => $sale->invoice_number,
            'createdAt' => $sale->created_at->format('d/m/Y H:i'),
            'createdAtFull' => $sale->created_at->format('d/m/Y H:i:s'),
            'cashier' => $sale->cashier?->name ?? '-',
            'statusOrder' => $sale->status_order ?? 'Take away',
            'serviceIdentity' => $sale->service_identity,
            'tableNumber' => $sale->table_number ?? '',
            'items' => $sale->items->map(fn($item) => [
                'name' => $item->product?->name ?? 'Produk dihapus',
                'qty' => $item->qty,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
            ])->toArray(),
            'subtotal' => $sale->subtotal,
            'discountAmount' => $sale->discount_amount,
            'totalAmount' => $sale->total_amount,
            'paymentMethod' => $sale->payment_method,
            'paidAmount' => $sale->paid_amount,
            'changeAmount' => $sale->change_amount,
            'status' => $sale->status,
        ]),

        init() {
            if (window.__thermalPrinter?.isConnected()) {
                this.btStatus = 'connected';
                this.btDeviceName = window.__thermalPrinter.getDeviceName();
                this.printBluetooth();
            }
        },

        async connectAndPrint() {
            try {
                this.btStatus = 'connecting';
                this.btError = '';
                if (!window.__thermalPrinter) {
                    window.__thermalPrinter = new ThermalPrinter(32);
                } else {
                    window.__thermalPrinter.charPerLine = 32;
                }
                if (!window.__thermalPrinter.isConnected()) {
                    await window.__thermalPrinter.connect();
                }
                this.btDeviceName = window.__thermalPrinter.getDeviceName();
                this.btStatus = 'connected';
                await this.printBluetooth();
            } catch (err) {
                this.btStatus = 'error';
                this.btError = err.message || 'Gagal menghubungkan printer.';
            }
        },

        async printBluetooth() {
            try {
                this.btStatus = 'printing';
                window.__thermalPrinter.buildReceipt(this.receiptData);
                await window.__thermalPrinter.send();
                this.btStatus = 'success';
                setTimeout(() => { this.btStatus = 'connected'; }, 2000);
            } catch (err) {
                this.btStatus = 'error';
                this.btError = err.message || 'Gagal mencetak.';
            }
        },

        get isConnected() {
            return window.__thermalPrinter?.isConnected() || false;
        },
    }">

    {{-- Action Bar --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden max-w-5xl mx-auto">
        <div class="flex items-center gap-3 sm:gap-4">
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm btn-circle">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
            <div>
                <h1 class="text-xl sm:text-2xl font-black">Invoice</h1>
                <p class="text-sm text-base-content/50">{{ $sale->invoice_number }}</p>
            </div>
        </div>
        <div class="flex flex-wrap w-full sm:w-auto items-center gap-2">
            {{-- Bluetooth Print --}}
            <button @click="isConnected ? printBluetooth() : connectAndPrint()"
                :disabled="btStatus === 'connecting' || btStatus === 'printing'"
                class="btn btn-primary btn-sm gap-2 flex-1 sm:flex-none min-w-0">
                <template x-if="btStatus === 'connecting' || btStatus === 'printing'">
                    <span class="loading loading-spinner loading-xs"></span>
                </template>
                <template x-if="btStatus !== 'connecting' && btStatus !== 'printing'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.348 14.652a3.75 3.75 0 0 1 0-5.304m5.304 0a3.75 3.75 0 0 1 0 5.304m-7.425 2.121a6.75 6.75 0 0 1 0-9.546m9.546 0a6.75 6.75 0 0 1 0 9.546M5.106 18.894c-3.808-3.807-3.808-9.98 0-13.788m13.788 0c3.808 3.807 3.808 9.98 0 13.788M12 12h.008v.008H12V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </template>
                <span class="truncate"
                    x-text="btStatus === 'connecting' ? 'Menghubungkan...' : btStatus === 'printing' ? 'Mencetak...' : btStatus === 'success' ? 'Terkirim ✓' : isConnected ? 'Cetak Bluetooth' : 'Hubungkan Printer'"></span>
            </button>
            {{-- Browser Print Fallback --}}
            <button onclick="window.print()" class="btn btn-ghost btn-sm gap-2 flex-1 sm:flex-none min-w-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                <span class="truncate">Cetak Browser</span>
            </button>
            <button wire:click="backToPOS" class="btn btn-ghost btn-sm gap-2 flex-1 sm:flex-none min-w-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182M2.985 19.644l3.181-3.183" />
                </svg>
                <span class="truncate">Transaksi Baru</span>
            </button>
        </div>
    </div>

    {{-- Bluetooth Status Banner --}}
    <div class="print:hidden max-w-5xl mx-auto mb-4" x-show="btDeviceName || btError" x-cloak>
        <div class="alert alert-sm" :class="btError ? 'alert-error' : 'alert-success'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-4 h-4 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.348 14.652a3.75 3.75 0 0 1 0-5.304m5.304 0a3.75 3.75 0 0 1 0 5.304m-7.425 2.121a6.75 6.75 0 0 1 0-9.546m9.546 0a6.75 6.75 0 0 1 0 9.546M5.106 18.894c-3.808-3.807-3.808-9.98 0-13.788m13.788 0c3.808 3.807 3.808 9.98 0 13.788M12 12h.008v.008H12V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            <span x-show="btDeviceName && !btError" x-text="'Terhubung ke: ' + btDeviceName"></span>
            <span x-show="btError" x-text="btError"></span>
        </div>
    </div>

    {{-- Content: 2 Column on Desktop --}}
    <div class="flex flex-col lg:flex-row gap-6 max-w-5xl mx-auto">

        {{-- LEFT: Struk / Receipt --}}
        <div class="w-full max-w-[350px] lg:w-[350px] shrink-0 mx-auto lg:mx-0 invoice-print-area">
            <div class="invoice-paper card bg-white text-black rounded-none border-0 shadow-lg lg:shadow-xl">
                <div class="invoice-body card-body p-4 sm:p-5 font-mono text-[12px]">

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
            </div>
        </div>

        {{-- RIGHT: Order Summary (screen only) --}}
        <div class="flex-1 space-y-4 print:hidden">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body p-4">
                        <div class="text-[10px] uppercase tracking-wider text-base-content/40 font-bold">Total</div>
                        <div class="text-xl font-black text-primary">Rp
                            {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </div>
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
                                            <img src="{{ Storage::url($item->product->foto_product) }}"
                                                alt="{{ $item->product->name }}" class="object-cover w-full h-full" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <x-heroicon-o-photo class="w-4 h-4 text-base-content/20" />
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="grow min-w-0">
                                    <p class="font-bold text-sm truncate">{{ $item->product?->name ?? 'Produk dihapus' }}
                                    </p>
                                    <p class="text-xs text-base-content/50">{{ $item->qty }} × Rp
                                        {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
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
                        @if ($sale->status !== 'unpaid')
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
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="isConnected ? printBluetooth() : connectAndPrint()"
                    :disabled="btStatus === 'connecting' || btStatus === 'printing'"
                    class="btn btn-primary gap-2 flex-1">
                    <template x-if="btStatus === 'connecting' || btStatus === 'printing'">
                        <span class="loading loading-spinner loading-sm"></span>
                    </template>
                    <template x-if="btStatus !== 'connecting' && btStatus !== 'printing'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.348 14.652a3.75 3.75 0 0 1 0-5.304m5.304 0a3.75 3.75 0 0 1 0 5.304m-7.425 2.121a6.75 6.75 0 0 1 0-9.546m9.546 0a6.75 6.75 0 0 1 0 9.546M5.106 18.894c-3.808-3.807-3.808-9.98 0-13.788m13.788 0c3.808 3.807 3.808 9.98 0 13.788M12 12h.008v.008H12V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </template>
                    <span
                        x-text="btStatus === 'connecting' ? 'Menghubungkan...' : btStatus === 'printing' ? 'Mencetak...' : btStatus === 'success' ? 'Terkirim ✓' : isConnected ? 'Cetak Bluetooth' : 'Hubungkan Printer'"></span>
                </button>
                <button onclick="window.print()" class="btn btn-ghost gap-2 flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    Cetak Browser
                </button>
                <button wire:click="backToPOS" class="btn btn-ghost gap-2 flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182M2.985 19.644l3.181-3.183" />
                    </svg>
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
            html,
            body {
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
            .invoice-page>div {
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
                padding: 0.5mm 1mm !important;
                overflow: visible !important;
            }

            .invoice-body::before {
                display: none !important;
            }

            /* Remove gaps on flex items for print */
            .invoice-body .space-y-1 {
                gap: 0 !important;
            }

            .invoice-body .flex {
                gap: 0 !important;
            }

            .invoice-body .flex span:last-child {
                margin-left: auto !important;
            }

            /* Table */
            .invoice-print-area table {
                table-layout: fixed !important;
                width: 100% !important;
                margin: 0 !important;
                border-collapse: collapse !important;
            }

            .invoice-print-area table th:nth-child(1),
            .invoice-print-area table td:nth-child(1) {
                width: 52% !important;
            }

            .invoice-print-area table th:nth-child(2),
            .invoice-print-area table td:nth-child(2) {
                width: 12% !important;
            }

            .invoice-print-area table th:nth-child(3),
            .invoice-print-area table td:nth-child(3) {
                width: 36% !important;
            }

            .invoice-print-area table td,
            .invoice-print-area table th {
                padding: 1mm 0.5mm !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Remove divider gaps */
            .invoice-body .border-t {
                margin: 1mm 0 !important;
            }
        }
    </style>

    <script src="{{ asset('js/thermal-printer.js') }}?v={{ now()->timestamp }}"></script>
</div>