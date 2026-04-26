<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appName = \App\Models\AppSetting::get('app_name', config('app.name', 'Siska App'));
    @endphp

    <title>{{ $appName }} - {{ $title ?? 'Dashboard' }}</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('img/favicon/site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- PWA -->
    @pwaHead
    @livewireStyles
</head>

<body class="min-h-screen">
    <div class="{{ Auth::check() ? 'drawer' : 'drawer' }}"
        x-data="{
            sidebarOpen: false,
            isLg: window.innerWidth >= 1024,
            init() {
                const saved = localStorage.getItem('sidebar:open');
                if (saved !== null) {
                    this.sidebarOpen = saved === 'true';
                } else {
                    this.sidebarOpen = this.isLg;
                }
                this.syncCheckbox();

                window.addEventListener('resize', () => {
                    this.isLg = window.innerWidth >= 1024;
                });
            },
            toggle() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('sidebar:open', this.sidebarOpen);
                this.syncCheckbox();
            },
            syncCheckbox() {
                const cb = document.getElementById('sidebar-drawer');
                if (cb) cb.checked = this.sidebarOpen;
            }
        }"
        :class="sidebarOpen && isLg ? 'drawer-open' : ''"
        @sidebar-toggle.window="toggle()">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle"
            @change="sidebarOpen = $el.checked; localStorage.setItem('sidebar:open', sidebarOpen)" />

        <!-- Content -->
        <div class="drawer-content flex flex-col">
            <x-partials.navbar />

            <!-- Pull to Refresh Indicator -->
            <div id="pull-to-refresh"
                class="fixed top-16 left-0 right-0 z-40 flex justify-center transition-all duration-300 opacity-0 -translate-y-full pointer-events-none">
                <div class="bg-base-100 shadow-lg rounded-b-xl px-6 py-3 flex items-center gap-3">
                    <span class="loading loading-ring loading-md"></span>
                    <span class="text-sm font-medium">Memuat ulang data...</span>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 p-4 md:p-6 bg-base-200">
                <div class="w-full mx-auto">
                    <!-- Page Header -->
                    @if (isset($header))
                        {{ $header }}
                    @else
                        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h1 class="text-xl font-bold text-base-content">{{ $title ?? 'Dashboard' }}</h1>

                            <!-- Breadcrumbs -->
                            <div class="breadcrumbs text-sm">
                                <ul>
                                    <li>
                                        <a wire:navigate href="/">
                                            <x-heroicon-o-home class="w-4 h-4" />
                                            Dashboard
                                        </a>
                                    </li>
                                    @php
                                        $currentRoute = request()->route();
                                        $routeName = $currentRoute ? $currentRoute->getName() : null;
                                        $segments = request()->segments();
                                        $breadcrumbs = [];

                                        // Mapping untuk label yang lebih baik
                                        $labelMapping = [
                                            'materials' => 'Bahan Baku',
                                            'material-stocks' => 'Stok Material',
                                            'products' => 'Data Produk',
                                            'product-stocks' => 'Stok Produk',
                                            'productions' => 'Produksi',
                                            'sales' => 'Penjualan',
                                            'pos' => 'Kasir (POS)',
                                            'reports' => 'Laporan',
                                            'purchases' => 'Pembelian',
                                            'master' => 'Master Data',
                                            'users' => 'Manajemen User',
                                            'roles' => 'Hak Akses',
                                            'create' => 'Tambah',
                                            'edit' => 'Edit',
                                            'show' => 'Detail',
                                        ];

                                        $url = '';
                                        foreach ($segments as $key => $segment) {
                                            $url .= '/' . $segment;

                                            // Skip jika segment adalah angka (ID)
                                            if (is_numeric($segment)) {
                                                continue;
                                            }

                                            // Dapatkan label yang sesuai
                                            $label =
                                                $labelMapping[$segment] ?? ucwords(str_replace('-', ' ', $segment));

                                            $breadcrumbs[] = [
                                                'url' => $url,
                                                'label' => $label,
                                                'isLast' => $key === count($segments) - 1,
                                            ];
                                        }
                                    @endphp
                                    @foreach ($breadcrumbs as $crumb)
                                        <li>
                                            @if ($crumb['isLast'] || $loop->last)
                                                <span class="font-medium">{{ $crumb['label'] }}</span>
                                            @else
                                                <a wire:navigate href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Page Content -->
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <footer class="footer footer-center p-4 text-base-content bg-base-100 border-t border-base-300">
                <aside>
                    <p>Copyright © {{ date('Y') }} - {{ $appName }}. All rights
                        reserved.</p>
                </aside>
            </footer>
        </div>

        {{-- Admin Sidebar: hanya muncul untuk user yang login via Laravel Auth.
             Untuk siswa yang login via PIN, sidebar tidak dirender → Spatie @can
             otomatis mengembalikan false untuk guest, menu kosong. --}}
        <x-partials.sidebar />

    </div>

    {{-- Global Toast Notification --}}
    <x-partials.toast :success="session('success')" :error="session('error')" />

    {{-- PIN Student Logout Confirmation Dialog --}}
    @if (session('pos_student_id'))
        <dialog id="pin-logout-confirm" class="modal">
            <div class="modal-box max-w-xs rounded-3xl text-center">
                <x-heroicon-o-arrow-right-on-rectangle class="w-12 h-12 mx-auto text-error mb-2" />
                <h3 class="font-bold text-lg">Keluar dari Kasir?</h3>
                <p class="text-sm text-base-content/60 mt-1">Sesi kasir akan ditutup. Kamu perlu PIN lagi untuk masuk.
                </p>
                <div class="modal-action justify-center gap-3 mt-4">
                    <button class="btn btn-ghost btn-sm rounded-xl"
                        onclick="document.getElementById('pin-logout-confirm').close()">Batal</button>
                    <a href="{{ route('kasir.logout') }}" class="btn btn-error btn-sm rounded-xl">Ya, Keluar</a>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>
    @endif

    @livewireScripts
    @laravelPwa
    @pwaUpdateNotifier
    @pwaInstallButton
</body>

</html>
