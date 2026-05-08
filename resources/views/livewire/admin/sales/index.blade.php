<div wire:poll.6s>
    <div class="card bg-base-100 border border-base-300" style="overflow: visible !important;">
        <div class="card-body" style="overflow: visible !important;">
            @php
                $activeFilterCount = collect([
                    $filterStatus,
                    $filterOrderType,
                    $filterPaymentMethod,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-6">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="input input-sm">
                        <x-bi-search class="w-3" />
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari invoice atau customer..." />
                    </label>

                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5" />
                            Filter
                            @if ($activeFilterCount > 0)
                                <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
                            @endif
                        </label>
                        <div tabindex="0" class="dropdown-content z-10 card card-compact w-72 p-4 bg-base-100 border border-base-300 mt-2">
                            <div class="space-y-3">
                                <x-form.select
                                    label="Status Pembayaran"
                                    name="filterStatus"
                                    placeholder="Semua Status"
                                    wire:model.live="filterStatus"
                                    class="select-sm"
                                >
                                    <option value="paid">Lunas</option>
                                    <option value="unpaid">Hutang</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </x-form.select>

                                <x-form.select
                                    label="Tipe Order"
                                    name="filterOrderType"
                                    placeholder="Semua Tipe"
                                    wire:model.live="filterOrderType"
                                    class="select-sm"
                                >
                                    <option value="Take away">Take away</option>
                                    <option value="Dine in">Dine in</option>
                                </x-form.select>

                                <x-form.select
                                    label="Metode Pembayaran"
                                    name="filterPaymentMethod"
                                    placeholder="Semua Metode"
                                    wire:model.live="filterPaymentMethod"
                                    class="select-sm"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="qris">QRIS</option>
                                </x-form.select>

                                <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <a href="{{ route('kasir.pos') }}" wire:navigate class="btn btn-primary btn-sm gap-2">
                        <x-heroicon-o-plus-circle class="w-4 h-4" />
                        Buka POS (Kasir)
                    </a>
                </div>
            </div>

            <x-partials.table :columns="[
                ['label' => 'Invoice', 'field' => 'invoice_number', 'sortable' => true],
                ['label' => 'Identitas'],
                ['label' => 'Order'],
                ['label' => 'Meja'],
                ['label' => 'Total', 'field' => 'total_amount', 'sortable' => true],
                ['label' => 'Payment', 'field' => 'payment_method'],
                ['label' => 'Status', 'field' => 'status'],
                ['label' => 'Production'],
                ['label' => 'Tanggal', 'field' => 'created_at', 'sortable' => true],
                ['label' => 'Aksi', 'class' => 'text-center']
            ]" :data="$sales" :sortField="null"
                :sortDirection="null" emptyMessage="Tidak ada transaksi" emptyIcon="heroicon-o-shopping-bag">

                @foreach ($sales as $index => $sale)
                    @php
                        $canPaySale = auth()->user()?->can('sales.edit') || auth()->user()?->can('sales.manage');
                        $canDeleteSale = auth()->user()?->can('sales.delete') || auth()->user()?->can('sales.manage');

                        $customActions = [
                            ['method' => 'viewReceipt', 'label' => 'Cetak Struk', 'icon' => 'heroicon-o-printer'],
                        ];

                        if ($canPaySale && $sale->status === 'unpaid') {
                            $customActions[] = [
                                'method' => 'openPayment',
                                'label' => 'Pembayaran Hutang',
                                'icon' => 'heroicon-o-banknotes',
                                'class' => 'text-success',
                            ];
                        }

                        if ($canPaySale && $sale->production_status === 'delivered') {
                            $customActions[] = [
                                'method' => 'confirmCompleted',
                                'label' => 'Pesanan Selesai',
                                'icon' => 'heroicon-o-check-badge',
                                'class' => 'text-primary',
                            ];
                        }

                        $prodBadge = match($sale->production_status) {
                            'cooking' => 'badge-info',
                            'done' => 'badge-info',
                            'delivered' => 'badge-primary',
                            'completed' => 'badge-success',
                            default => 'badge-warning',
                        };
                    @endphp
                    <tr wire:key="sale-{{ $sale->id }}" class="hover:bg-base-200 transition-colors duration-150">
                        <td>{{ $sale->invoice_number }}</td>
                        <td>
                            {{ $sale->service_identity }}
                        </td>
                        <td>
                            <span class="badge badge-soft badge-info badge-sm">{{ $sale->status_order ?? 'Take away' }}</span>
                        </td>
                        <td>{{ $sale->status_order === 'Dine in' ? ($sale->table_number ?: '-') : '-' }}</td>
                        <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($sale->payment_method ?? '-') }}</td>
                        <td>
                            @if($sale->status === 'paid')
                                <span class="badge badge-soft badge-success badge-sm">Lunas</span>
                            @elseif($sale->status === 'unpaid')
                                <span class="badge badge-soft badge-warning badge-sm">Hutang</span>
                            @else
                                <span class="badge badge-soft badge-error badge-sm">Dibatalkan</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-soft {{ $prodBadge }} badge-sm">{{ $sale->production_status_label }}</span>
                        </td>
                        <td class="text-xs">{{ $sale->created_at->format('d/m/y H:i') }}</td>
                        <td class="text-center">
                            <x-partials.dropdown-action
                                :id="$sale->id"
                                :showView="true"
                                :viewRoute="route('kasir.invoice', $sale->id)"
                                :showEdit="false"
                                :showDelete="$canDeleteSale"
                                deleteMethod="confirmDelete"
                                :customActions="$customActions"
                            />
                        </td>
                    </tr>
                @endforeach

            </x-partials.table>

            <div class="mt-6 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-4">
                    <div class="text-sm text-gray-600 text-center sm:text-left">
                        Menampilkan <span class="font-semibold">{{ $sales->firstItem() ?? 0 }}</span>
                        sampai <span class="font-semibold">{{ $sales->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold">{{ $sales->total() }}</span> data
                    </div>

                    <x-partials.pagination :paginator="$sales" :perPage="$perPage" />
                </div>
            </div>
        </div>
    </div>

    <livewire:admin.sales.modals.receipt />
    <livewire:admin.sales.modals.payment />
    <livewire:admin.sales.modals.delete />

    {{-- Sound notification script for cashier --}}
    <script>
        (function () {
            const defaultVolume = Number(@js($soundVolume ?? 80));
            let selectedVoice = null;
            let activeTtsAudio = null;

            function getVoicesSafely() {
                if (!window.speechSynthesis) return [];
                try { return window.speechSynthesis.getVoices() || []; } catch (e) { return []; }
            }

            function scoreVoice(voice) {
                const name = String(voice?.name || '').toLowerCase();
                const lang = String(voice?.lang || '').toLowerCase();
                let score = 0;
                if (/^id([_-]id)?$/.test(lang) || lang.startsWith('id')) score += 100;
                if (/indonesia|bahasa/.test(name)) score += 60;
                if (/gadis|female|woman|zira|aria|siti|putri/.test(name)) score += 25;
                if (/male|man|pria/.test(name)) score -= 25;
                if (voice?.localService) score += 4;
                if (voice?.default) score += 2;
                return score;
            }

            function selectBestVoice(forceRefresh = false) {
                if (!forceRefresh && selectedVoice) return selectedVoice;
                const voices = getVoicesSafely();
                if (!voices.length) { selectedVoice = null; return null; }
                selectedVoice = [...voices].sort((a, b) => scoreVoice(b) - scoreVoice(a))[0] || null;
                return selectedVoice;
            }

            function normalizeIndonesianMessage(message) {
                return String(message || '')
                    .replace(/\bTake\s*away\b/gi, 'bawa pulang')
                    .replace(/\bDine\s*in\b/gi, 'makan di tempat')
                    .replace(/\bGuest\b/gi, 'tamu');
            }

            function playBeep(volumeValue) {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = 660;
                    gain.gain.value = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.start(); osc.stop(ctx.currentTime + 0.15);
                } catch (e) {}
            }

            function speakWithBrowser(message, volumeValue) {
                if (!window.speechSynthesis || !message) return;
                const voice = selectBestVoice();
                const utterance = new SpeechSynthesisUtterance(normalizeIndonesianMessage(message));
                utterance.lang = String(voice?.lang || 'id-ID');
                if (voice) utterance.voice = voice;
                utterance.rate = 1; utterance.pitch = 1;
                utterance.volume = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));
                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            }

            function speak(message, volumeValue) {
                const normalizedMessage = normalizeIndonesianMessage(message);
                const volume = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));
                try {
                    const ttsUrl = 'https://translate.googleapis.com/translate_tts?ie=UTF-8&client=tw-ob&tl=id&q=' + encodeURIComponent(normalizedMessage);
                    if (activeTtsAudio) { activeTtsAudio.pause(); activeTtsAudio = null; }
                    const audio = new Audio(ttsUrl);
                    audio.volume = volume;
                    activeTtsAudio = audio;
                    const p = audio.play();
                    if (p && typeof p.catch === 'function') { p.catch(() => speakWithBrowser(normalizedMessage, volumeValue)); }
                } catch (e) { speakWithBrowser(normalizedMessage, volumeValue); }
            }

            function bindCashierNotificationListener() {
                if (!window.Livewire || typeof window.Livewire.on !== 'function') return;
                if (typeof window.__cashierNotificationOff === 'function') { window.__cashierNotificationOff(); }
                window.__cashierNotificationOff = window.Livewire.on('play-cashier-notification', (payload) => {
                    const data = Array.isArray(payload) ? payload[0] : payload;
                    const volume = Number(data?.volume ?? defaultVolume);
                    const message = String(data?.message ?? 'Pesanan siap diantar.');
                    playBeep(volume);
                    setTimeout(() => speak(message, volume), 120);
                });
            }

            document.addEventListener('livewire:init', bindCashierNotificationListener);
            selectBestVoice(true);
            bindCashierNotificationListener();
        })();
    </script>
</div>