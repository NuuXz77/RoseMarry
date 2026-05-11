<div class="space-y-6" wire:poll.1s>
    <div class="card bg-base-100 border border-base-300">
        <div class="card-body p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
                <div>
                    <h1 class="card-title text-xl">Antrian Pesanan Masuk</h1>
                    <p class="text-sm text-base-content/70">
                        Halaman ini menampilkan pesanan dari cashier untuk diproses production, lengkap dengan notifikasi suara.
                    </p>
                </div>
                <div class="badge badge-soft badge-primary px-3 py-3 text-xs">
                    Live - Auto refresh
                </div>
            </div>

            @php
                $activeFilterCount = collect([
                    $filterStatus,
                    $filterService,
                ])->filter(fn($value) => $value !== '')->count();
            @endphp

            @if(!$soundEnabled)
                <div class="alert alert-warning mb-4">
                    <x-heroicon-o-speaker-x-mark class="w-5 h-5" />
                    <span>Notifikasi suara production saat ini nonaktif dari menu Sound Notification.</span>
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
                <div class="card bg-warning/10 border border-warning/30">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-warning font-bold">Pending</div>
                        <div class="text-2xl font-black">{{ $countPending }}</div>
                    </div>
                </div>
                <div class="card bg-info/10 border border-info/30">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-info font-bold">Cooking</div>
                        <div class="text-2xl font-black">{{ $countCooking }}</div>
                    </div>
                </div>
                <div class="card bg-success/10 border border-success/30">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-success font-bold">Selesai Masak</div>
                        <div class="text-2xl font-black">{{ $countDone }}</div>
                    </div>
                </div>
                <div class="card bg-primary/10 border border-primary/30">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-primary font-bold">Diantar</div>
                        <div class="text-2xl font-black">{{ $countDelivered }}</div>
                    </div>
                </div>
                <div class="card bg-base-200 border border-base-300">
                    <div class="card-body p-4">
                        <div class="text-xs uppercase tracking-wider text-base-content/60 font-bold">Selesai</div>
                        <div class="text-2xl font-black">{{ $countCompleted }}</div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-3 mb-4">
                <label class="input input-sm input-bordered flex items-center gap-2 w-full md:w-80">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="grow"
                        placeholder="Cari invoice / antrian / nama"
                    />
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
                                label="Status"
                                name="filterStatus"
                                wireModel="filterStatus"
                                wireModelModifier="live"
                                placeholder="Semua Status"
                                class="select-sm"
                            >
                                <option value="pending">Pending</option>
                                <option value="cooking">Cooking</option>
                                <option value="done">Selesai Masak</option>
                                <option value="delivered">Diantar</option>
                                <option value="completed">Selesai</option>
                            </x-form.select>

                            <x-form.select
                                label="Layanan"
                                name="filterService"
                                wireModel="filterService"
                                wireModelModifier="live"
                                placeholder="Semua Layanan"
                                class="select-sm"
                            >
                                <option value="Take away">Take away</option>
                                <option value="Dine in">Dine in</option>
                            </x-form.select>

                            <button wire:click="resetFilters" class="btn btn-ghost btn-sm w-full">Reset Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $columns = [
                    ['label' => 'Invoice', 'field' => 'invoice_number'],
                    ['label' => 'Waktu', 'field' => 'created_at'],
                    ['label' => 'Layanan', 'field' => 'status_order'],
                    ['label' => 'Identitas', 'field' => 'service_identity'],
                    ['label' => 'Status Production', 'field' => 'production_status'],
                    ['label' => 'Aksi', 'class' => 'text-center'],
                ];
            @endphp

            <x-partials.table :columns="$columns" :data="$orders" emptyMessage="Belum ada pesanan yang masuk.">
                @foreach($orders as $order)
                    @php
                        $prodActions = [];

                        if ($canManage && $order->production_status === 'pending') {
                            $prodActions[] = ['method' => 'setCooking', 'label' => 'Proses Pesanan', 'icon' => 'heroicon-o-fire', 'class' => 'text-info'];
                        }
                        if ($canManage && $order->production_status === 'cooking') {
                            $prodActions[] = ['method' => 'setDone', 'label' => 'Selesai Masak', 'icon' => 'heroicon-o-check-circle', 'class' => 'text-success'];
                        }
                        if ($canManage && $order->production_status === 'done') {
                            $prodActions[] = ['method' => 'setDelivered', 'label' => 'Sudah Diantar', 'icon' => 'heroicon-o-truck', 'class' => 'text-primary'];
                        }
                        if ($order->production_status === 'delivered') {
                            $prodActions[] = ['method' => 'setCompleted', 'label' => 'Pesanan Selesai', 'icon' => 'heroicon-o-check-badge', 'class' => 'text-neutral'];
                        }
                    @endphp
                    <tr wire:key="prod-order-{{ $order->id }}">
                        <td class="font-semibold">{{ $order->invoice_number }}</td>
                        <td class="text-xs">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge badge-soft {{ $order->status_order === 'Take away' ? 'badge-warning' : 'badge-info' }}">
                                {{ $order->status_order }}
                            </span>
                        </td>
                        <td>
                            <div class="font-semibold text-sm">{{ $order->service_identity }}</div>
                            @if($order->status_order === 'Dine in' && $order->table_number)
                                <div class="text-xs text-base-content/60">Meja: {{ $order->table_number }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusBadge = match($order->production_status) {
                                    'cooking' => 'badge-info',
                                    'done' => 'badge-success',
                                    'delivered' => 'badge-primary',
                                    'completed' => 'badge-primary',
                                    default => 'badge-warning',
                                };
                            @endphp
                            <span class="badge badge-soft {{ $statusBadge }}">{{ $order->production_status_label }}</span>
                        </td>
                        <td class="text-center">
                            <x-partials.dropdown-action
                                :id="$order->id"
                                :showView="true"
                                :viewRoute="route('production.orders.detail', $order->id)"
                                :showEdit="false"
                                :showDelete="false"
                                :customActions="$prodActions"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-partials.table>

            <div class="mt-4">
                <x-partials.pagination :paginator="$orders" />
            </div>
        </div>
    </div>

    <script>
        (function () {
            const defaultVolume = Number(@js($soundVolume ?? 80));
            let selectedVoice = null;
            let activeTtsAudio = null;

            function getVoicesSafely() {
                if (!window.speechSynthesis) return [];
                try {
                    return window.speechSynthesis.getVoices() || [];
                } catch (e) {
                    return [];
                }
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
                if (!voices.length) {
                    selectedVoice = null;
                    return null;
                }

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
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    oscillator.type = 'sine';
                    oscillator.frequency.value = 880;
                    gainNode.gain.value = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));

                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    oscillator.start();
                    oscillator.stop(ctx.currentTime + 0.12);
                } catch (e) {
                    // ignore beep errors
                }
            }

            function speakWithBrowser(message, volumeValue) {
                if (!window.speechSynthesis || !message) return;

                const voice = selectBestVoice();

                const utterance = new SpeechSynthesisUtterance(normalizeIndonesianMessage(message));
                utterance.lang = String(voice?.lang || 'id-ID');
                if (voice) {
                    utterance.voice = voice;
                }
                utterance.rate = 1;
                utterance.pitch = 1;
                utterance.volume = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));

                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            }

            function speak(message, volumeValue) {
                const normalizedMessage = normalizeIndonesianMessage(message);
                const volume = Math.max(0, Math.min(1, (Number(volumeValue ?? defaultVolume) || 80) / 100));

                // Primary engine: Google Indonesian TTS (lebih konsisten bahasa Indonesia).
                try {
                    const ttsUrl = 'https://translate.googleapis.com/translate_tts?ie=UTF-8&client=tw-ob&tl=id&q=' + encodeURIComponent(normalizedMessage);
                    if (activeTtsAudio) {
                        activeTtsAudio.pause();
                        activeTtsAudio = null;
                    }

                    const audio = new Audio(ttsUrl);
                    audio.volume = volume;
                    activeTtsAudio = audio;

                    const playPromise = audio.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(() => {
                            speakWithBrowser(normalizedMessage, volumeValue);
                        });
                    }
                } catch (e) {
                    speakWithBrowser(normalizedMessage, volumeValue);
                }
            }

            function bindNotificationListener() {
                if (!window.Livewire || typeof window.Livewire.on !== 'function') {
                    return;
                }

                if (typeof window.__productionOrderNotificationOff === 'function') {
                    window.__productionOrderNotificationOff();
                }

                window.__productionOrderNotificationOff = window.Livewire.on('play-order-notification', (payload) => {
                    const data = Array.isArray(payload) ? payload[0] : payload;
                    const volume = Number(data?.volume ?? defaultVolume);
                    const message = String(data?.message ?? 'Ada pembaruan pesanan.');

                    playBeep(volume);
                    setTimeout(() => speak(message, volume), 120);
                });
            }

            document.addEventListener('livewire:init', bindNotificationListener);
            selectBestVoice(true);
            bindNotificationListener();
        })();
    </script>
</div>
