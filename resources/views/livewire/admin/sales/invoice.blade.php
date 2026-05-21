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

        // Share & Export
        shareStatus: 'idle',
        copyStatus: 'idle',
        showCopyModal: false,

        getInvoiceText() {
            const d = this.receiptData;
            const fmt = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
            let t = '*' + d.appName + '*\n';
            t += 'Invoice: ' + d.invoiceNumber + '\n';
            t += d.createdAt + '\n\n';
            d.items.forEach(i => {
                t += '• ' + i.name + ' (' + i.qty + 'x) — ' + fmt(i.subtotal) + '\n';
            });
            t += '\n';
            if (d.discountAmount > 0) {
                t += 'Subtotal: ' + fmt(d.subtotal) + '\n';
                t += 'Diskon: -' + fmt(d.discountAmount) + '\n';
            }
            t += '*Total: ' + fmt(d.totalAmount) + '*\n';
            t += 'Bayar: ' + d.paymentMethod.toUpperCase() + ' — ' + (d.status === 'paid' ? '✅ Lunas' : d.status === 'unpaid' ? '⏳ Hutang' : '❌ Batal') + '\n';
            return t;
        },

        async copyText() {
            try {
                await navigator.clipboard.writeText(this.getInvoiceText());
                this.copyStatus = 'success';
                setTimeout(() => { this.copyStatus = 'idle'; }, 2000);
            } catch (e) {
                // Fallback
                const ta = document.createElement('textarea');
                ta.value = this.getInvoiceText();
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.copyStatus = 'success';
                setTimeout(() => { this.copyStatus = 'idle'; }, 2000);
            }
        },

        async captureInvoice() {
            const el = document.querySelector('.invoice-paper');
            if (!el) return null;
            const html2canvas = (await import('https://cdn.jsdelivr.net/npm/html2canvas-pro@1.5.8/+esm')).default;
            return await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
        },

        async downloadJPG() {
            this.shareStatus = 'jpg';
            try {
                const canvas = await this.captureInvoice();
                if (!canvas) return;
                const link = document.createElement('a');
                link.download = this.receiptData.invoiceNumber + '.jpg';
                link.href = canvas.toDataURL('image/jpeg', 0.95);
                link.click();
            } catch (e) { console.error(e); }
            this.shareStatus = 'idle';
        },

        async downloadPDF() {
            this.shareStatus = 'pdf';
            try {
                const canvas = await this.captureInvoice();
                if (!canvas) return;
                const { jsPDF } = await import('https://cdn.jsdelivr.net/npm/jspdf@2.5.2/+esm');
                const imgW = canvas.width;
                const imgH = canvas.height;
                const pdfW = 80; // mm receipt width
                const pdfH = (imgH * pdfW) / imgW;
                const doc = new jsPDF({ unit: 'mm', format: [pdfW, pdfH + 10] });
                doc.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 5, pdfW, pdfH);
                doc.save(this.receiptData.invoiceNumber + '.pdf');
            } catch (e) { console.error(e); }
            this.shareStatus = 'idle';
        },

        shareWhatsApp() {
            const text = this.getInvoiceText();
            const encoded = encodeURIComponent(text);
            window.open('https://wa.me/?text=' + encoded, '_blank');
        },

        shareEmail() {
            const d = this.receiptData;
            const subject = encodeURIComponent('Invoice ' + d.invoiceNumber + ' - ' + d.appName);
            const body = encodeURIComponent(this.getInvoiceText());
            window.open('mailto:?subject=' + subject + '&body=' + body, '_self');
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
                        @if ($sale->catatan)
                            <div class="flex justify-between border-b border-base-200 pb-2 mb-2">
                                <span class="text-base-content/60">Catatan</span>
                                <span class="font-bold text-warning">{{ $sale->catatan }}</span>
                            </div>
                        @endif
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

            {{-- Share & Export --}}
            <div class="card bg-base-100 border border-base-300">
                <div class="card-body p-0">
                    <div class="px-5 py-3 border-b border-base-200">
                        <h3 class="font-bold text-sm flex items-center gap-2">
                            <x-heroicon-o-share class="w-4 h-4 text-primary" />
                            Bagikan & Ekspor
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        {{-- Download Row --}}
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="downloadPDF()" :disabled="shareStatus !== 'idle'"
                                class="btn btn-sm btn-outline btn-error gap-2 w-full">
                                <template x-if="shareStatus === 'pdf'">
                                    <span class="loading loading-spinner loading-xs"></span>
                                </template>
                                <template x-if="shareStatus !== 'pdf'">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </template>
                                <span x-text="shareStatus === 'pdf' ? 'Proses...' : 'Unduh PDF'"></span>
                            </button>
                            <button @click="downloadJPG()" :disabled="shareStatus !== 'idle'"
                                class="btn btn-sm btn-outline btn-info gap-2 w-full">
                                <template x-if="shareStatus === 'jpg'">
                                    <span class="loading loading-spinner loading-xs"></span>
                                </template>
                                <template x-if="shareStatus !== 'jpg'">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V4.5a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v15a1.5 1.5 0 0 0 1.5 1.5Z" />
                                    </svg>
                                </template>
                                <span x-text="shareStatus === 'jpg' ? 'Proses...' : 'Unduh JPG'"></span>
                            </button>
                        </div>
                        {{-- Share Row --}}
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="shareWhatsApp()"
                                class="btn btn-sm btn-outline btn-success gap-2 w-full">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                                </svg>
                                WhatsApp
                            </button>
                            <button @click="shareEmail()"
                                class="btn btn-sm btn-outline btn-warning gap-2 w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                Email
                            </button>
                        </div>
                        {{-- Copy Text --}}
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="copyText()"
                                class="btn btn-sm btn-outline gap-2 w-full"
                                :class="copyStatus === 'success' ? 'btn-success' : ''">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                </svg>
                                <span x-text="copyStatus === 'success' ? 'Tersalin ✓' : 'Salin Teks'"></span>
                            </button>
                            <button @click="showCopyModal = true"
                                class="btn btn-sm btn-outline btn-ghost gap-2 w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Lihat Teks
                            </button>
                        </div>
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

    {{-- Copy Text Modal --}}
    <div x-show="showCopyModal" x-cloak x-transition.opacity
        class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 print:hidden"
        @click.self="showCopyModal = false"
        @keydown.escape.window="showCopyModal = false">
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col overflow-hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="px-5 py-4 border-b border-base-200 flex items-center justify-between shrink-0">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary" />
                    Preview Teks Invoice
                </h3>
                <button @click="showCopyModal = false" class="btn btn-ghost btn-sm btn-circle">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <pre class="bg-base-200 text-base-content rounded-xl p-4 text-xs font-mono whitespace-pre-wrap leading-relaxed select-all" x-text="getInvoiceText()"></pre>
            </div>
            <div class="px-5 py-3 border-t border-base-200 flex gap-2 shrink-0">
                <button @click="copyText(); showCopyModal = false"
                    class="btn btn-primary btn-sm flex-1 gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                    Salin & Tutup
                </button>
                <button @click="showCopyModal = false" class="btn btn-ghost btn-sm">Tutup</button>
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