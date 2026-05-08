<div>
    <x-form.modal
        modalId="receipt-modal"
        title="Struk Pembayaran"
        modalSize="modal-box w-11/12 max-w-[350px]"
        :showButton="false"
        :showSaveButton="false">

        @if($sale)
        <div class="flex flex-col items-center">
            <div id="thermal-receipt" class="bg-white text-black p-4 sm:p-5 w-full font-mono text-[12px] border shadow-inner">

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

            <div class="modal-action w-full flex justify-center gap-2 mt-6">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('receipt-modal').close()">Tutup</button>
                <button type="button" class="btn btn-primary gap-2" onclick="printReceipt()">
                    <x-heroicon-o-printer class="w-5 h-5" />
                    Cetak Struk
                </button>
            </div>
        </div>
        @endif
    </x-form.modal>

    <script>
        document.addEventListener('livewire:initialized', () => {
            window.printReceipt = function() {
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
                                @page { size: 80mm auto; margin: 2mm; }
                                html, body {
                                    width: 80mm;
                                    margin: 0;
                                    padding: 0;
                                    font-family: monospace;
                                    font-size: 12px;
                                    color: #000;
                                    background: #fff;
                                }
                                .receipt-wrap {
                                    width: 76mm;
                                    margin: 0 auto;
                                    padding: 2mm 0;
                                }
                                .text-center { text-align: center; }
                                .font-bold { font-weight: bold; }
                                .flex { display: flex; }
                                .justify-between { justify-content: space-between; }
                                .border-b { border-bottom: 1px dashed black; }
                                .my-2 { margin: 8px 0; }
                                .uppercase { text-transform: uppercase; }
                                @media print {
                                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                                }
                            </style>
                        </head>
                        <body><div class="receipt-wrap">${printContent}</div></body>
                    </html>
                `);
                doc.close();
                iframe.contentWindow.focus(); iframe.contentWindow.print();
                setTimeout(() => { document.body.removeChild(iframe); }, 500);
            };
        });
    </script>
</div>
