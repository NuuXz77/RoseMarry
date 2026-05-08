<div x-data="pwaSetup()" x-init="initSetup()">
    <!-- Onboarding / Permissions Modal -->
    <dialog id="pwa-onboarding-modal" class="modal modal-bottom sm:modal-middle backdrop-blur-sm"
        :class="showOnboarding ? 'modal-open' : ''">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-primary flex items-center gap-2">
                <x-heroicon-s-rocket-launch class="w-6 h-6" /> Selamat Datang!
            </h3>
            <p class="py-4 text-sm">
                Untuk pengalaman aplikasi kasir (POS) yang maksimal, sistem membutuhkan izin pada perangkat Anda:
            </p>
            <ul class="space-y-3 mb-6">
                <li class="flex items-start gap-3">
                    <div class="bg-success/20 p-2 rounded-lg text-success shrink-0">
                        <x-heroicon-s-speaker-wave class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">Notifikasi Suara</h4>
                        <p class="text-xs opacity-70">Sistem perlu izin memutar suara untuk notifikasi pesanan masuk dan
                            peringatan stok.</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <div class="bg-info/20 p-2 rounded-lg text-info shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m7 7 10 10-5 5V2l5 5-10 10" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">Koneksi Bluetooth</h4>
                        <p class="text-xs opacity-70">Pastikan Bluetooth Anda aktif jika menggunakan printer kasir
                            (Thermal Printer) untuk struk.</p>
                    </div>
                </li>
            </ul>
            <div class="modal-action mt-0">
                <button class="btn btn-primary w-full shadow-lg" @click="grantPermissions()">
                    Mengerti & Izinkan Suara
                </button>
            </div>
        </div>
    </dialog>

    <!-- Install PWA Toast (Small, non-blocking) -->
    <div x-show="showInstallPrompt" style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-10"
         class="fixed bottom-4 right-4 z-[9999] max-w-sm w-[calc(100%-2rem)] md:w-80 bg-base-100 shadow-2xl rounded-2xl border border-base-300 p-3 flex gap-3 items-center">
        
        <!-- App Icon -->
        <div class="bg-primary/10 w-12 h-12 rounded-xl flex items-center justify-center shrink-0 overflow-hidden p-1">
            <img src="{{ asset('img/logo.png') }}" class="w-full h-full object-contain" alt="Logo" />
        </div>

        <!-- Text content -->
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-sm leading-none mb-1 text-base-content">RoseMarry App</h4>
            <p class="text-[10px] text-base-content/70 leading-tight">Install untuk akses lebih cepat & offline.</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-1 shrink-0">
            <button class="btn btn-primary btn-sm rounded-lg text-xs" @click="installPwa()">
                Install
            </button>
            <button class="btn btn-ghost btn-xs btn-square text-base-content/50 hover:text-error" @click="dismissInstall()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pwaSetup', () => ({
            showOnboarding: false,
            showInstallPrompt: false,
            deferredPrompt: null,

            initSetup() {
                const onboardingDone = localStorage.getItem('pwa_onboarding_done_v1');
                if (!onboardingDone) {
                    this.showOnboarding = true;
                }

                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.checkAndShowInstall();
                });

                window.addEventListener('appinstalled', () => {
                    this.showInstallPrompt = false;
                    this.deferredPrompt = null;
                });

                // Force showing the toast after a short delay (for testing and manual install fallback)
                setTimeout(() => {
                    this.checkAndShowInstall();
                }, 2000);
            },

            checkAndShowInstall() {
                const installDismissed = sessionStorage.getItem('pwa_install_dismissed');
                if (!this.showOnboarding && !installDismissed && !this.isRunningStandalone() && !this.showInstallPrompt) {
                    this.showInstallPrompt = true;
                }
            },

            grantPermissions() {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioCtx.createOscillator();
                    oscillator.type = 'sine';
                    oscillator.frequency.value = 440;
                    const gainNode = audioCtx.createGain();
                    gainNode.gain.value = 0.01;
                    oscillator.connect(gainNode);
                    gainNode.connect(audioCtx.destination);
                    oscillator.start();
                    oscillator.stop(audioCtx.currentTime + 0.05);
                } catch (e) {
                    console.log('AudioContext init skipped/error', e);
                }

                localStorage.setItem('pwa_onboarding_done_v1', 'true');
                this.showOnboarding = false;
                
                setTimeout(() => {
                    this.checkAndShowInstall();
                }, 1000);
            },

            async installPwa() {
                if (this.deferredPrompt) {
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        this.showInstallPrompt = false;
                    }
                    this.deferredPrompt = null;
                } else {
                    alert('Browser Anda tidak mendukung install otomatis. Silakan gunakan menu "Add to Home Screen" (Tambahkan ke Layar Utama) atau tombol Bagikan di browser Anda.');
                }
            },

            dismissInstall() {
                this.showInstallPrompt = false;
                sessionStorage.setItem('pwa_install_dismissed', 'true');
            },

            isRunningStandalone() {
                return (window.matchMedia('(display-mode: standalone)').matches) || (window.navigator.standalone) || document.referrer.includes('android-app://');
            }
        }));
    });
</script>