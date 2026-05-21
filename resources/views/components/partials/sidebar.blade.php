<aside class="drawer-side z-40">
    <label for="sidebar-drawer" class="drawer-overlay" @click="$dispatch('sidebar-toggle')"></label>

    <div class="bg-base-100 text-base-content h-full w-64 md:w-72 border-r border-base-300 flex flex-col">
        <!-- Sidebar Header - Fixed at Top -->
        <div class="flex items-center gap-3 border-b border-base-300 bg-base-100 sticky top-0 z-10 navbar">
            {{-- <div class="bg-primary text-primary-content p-1 rounded-xl">
                {{-- <x-heroicon-o-presentation-chart-line class="w-5" />
                {{-- gunakan logo
            </div> --}}
            <img src="{{ asset('img/logo.png') }}" alt="App Logo" class="w-8" />
            <div class="flex-1 min-w-0">
                @php
                    $appName = \App\Models\AppSetting::get('app_name', config('app.name', 'Rosemary POS'));
                @endphp
                <h2 class="text-xl font-bold truncate">{{ $appName }}</h2>
                <p class="text-xs text-gray-500">v1.0.0</p>
            </div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto scrollbar-hide p-1">
            <!-- User Info -->
            {{-- <div class="bg-base-200 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <div class="avatar placeholder mr-3">
                        <div class="bg-primary text-primary-content rounded-full w-12">
                            @if (Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="User Avatar" />
                            @else
                                <span class="text-lg font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="font-bold truncate">{{ Auth::user()->username }}</p>
                        <p class="text-sm text-gray-500">
                            <span
                                class="badge badge-sm 
                                {{ Auth::user()->hasRole('Admin') ? 'badge-primary' : 'badge-secondary' }}">
                                {{ Auth::user()->getRoleNames()->first() ?? 'No Role' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div> --}}

            <!-- Menu Navigation -->
            <ul class="menu sidebar-menu space-y-1 w-full">
                @php
                    $user = auth()->user();

                    // Safe defaults so Blade never hits undefined-variable notices.
                    $hideSchedulingMenu = false;
                    $canUsers = false;
                    $canCategoryPermissions = false;
                    $canRoles = false;
                    $canPermissions = false;
                    $userManagementItems = 0;

                    $canStudents = false;
                    $canStudentGroups = false;
                    $canStudentMembers = false;
                    $studentItems = 0;

                    $canSchedules = false;
                    $canGroupAttendances = false;
                    $canGuides = false;
                    $canGuideManage = false;

                    $canMaterialGroup = false;
                    $canProductGroup = false;
                    $canRecipe = false;
                    $inventoryItems = 0;

                    $isCashierRole = false;
                    $canPurchases = false;
                    $canProductions = false;
                    $canProductionOrders = false;
                    $canSales = false;
                    $transactionItems = 0;

                    $canReportSales = false;
                    $canReportCustomers = false;
                    $canReportPurchases = false;
                    $canReportProductions = false;
                    $canReportStocks = false;
                    $canReportSchedules = false;
                    $reportItems = 0;

                    $canMasterClasses = false;
                    $canMasterCategories = false;
                    $canMasterUnits = false;
                    $canMasterSuppliers = false;
                    $canMasterCustomers = false;
                    $canMasterShifts = false;
                    $canMasterDivisions = false;
                    $hasMasterMenu = false;

                    $canAppSettings = false;
                    $canDiscountSettings = false;
                    $canActivityLogs = false;
                    $canSoundNotifications = false;
                    $canSystemLogs = false;
                    $settingsItems = 0;

                    if ($user) {
                        $hideSchedulingMenu = (bool) $user->hasAnyRole(['Production', 'production']);

                        $canUsers = $user->can('users.manage');
                        $canCategoryPermissions = $user->can('permissions.manage');
                        $canRoles = $user->can('roles.view') || $user->can('roles.manage');
                        $canPermissions = $user->can('permissions.view') || $user->can('permissions.manage');
                        $userManagementItems =
                            ($canUsers ? 1 : 0) + ($canCategoryPermissions || $canRoles || $canPermissions ? 1 : 0);

                        $canStudents = $user->can('students.view');
                        $canStudentGroups = $user->can('student-groups.view');
                        $canStudentMembers = $user->can('student-group-members.view');
                        $studentItems = ($canStudents ? 1 : 0) + ($canStudentGroups ? 1 : 0) + ($canStudentMembers ? 1 : 0);

                        $canSchedules = !$hideSchedulingMenu && $user->can('schedules.view');
                        $canGroupAttendances = !$hideSchedulingMenu && $user->can('student-group-attendances.view');
                        $canGuides = $user->can('guides.view');
                        $canGuideManage = $user->can('guides.manage');

                        $canMaterialGroup =
                            $user->can('materials.view') ||
                            $user->can('material-stocks.view') ||
                            $user->can('material-stock-logs.view') ||
                            $user->can('material-wastes.view');
                        $canProductGroup =
                            $user->can('products.view') ||
                            $user->can('product-stocks.view') ||
                            $user->can('product-stock-logs.view') ||
                            $user->can('product-wastes.view');
                        $canRecipe = $user->can('product-materials.view');
                        $inventoryItems = ($canMaterialGroup ? 1 : 0) + ($canProductGroup ? 1 : 0) + ($canRecipe ? 1 : 0);

                        $isCashierRole = $user->hasRole('Cashier');
                        $canPurchases = !$isCashierRole && $user->can('purchases.view');
                        $canProductions = !$isCashierRole && $user->can('productions.view');
                        $canProductionOrders = $user->can('production-orders.view');
                        $canSales = !$isCashierRole && $user->can('sales.view');
                        $transactionItems =
                            ($canPurchases ? 1 : 0) +
                            ($canProductions ? 1 : 0) +
                            ($canProductionOrders ? 1 : 0) +
                            ($canSales ? 1 : 0);

                        $canReportSales = $user->can('reports.sales.view');
                        $canReportCustomers = $user->can('reports.customers.view');
                        $canReportPurchases = $user->can('reports.purchases.view');
                        $canReportProductions = $user->can('reports.productions.view');
                        $canReportStocks = $user->can('reports.stocks.view');
                        $canReportSchedules = !$hideSchedulingMenu && $user->can('reports.schedules.view');
                        $reportItems =
                            ($canReportSales ? 1 : 0) +
                            ($canReportCustomers ? 1 : 0) +
                            ($canReportPurchases ? 1 : 0) +
                            ($canReportProductions ? 1 : 0) +
                            ($canReportStocks ? 1 : 0) +
                            ($canReportSchedules ? 1 : 0);

                        $canMasterClasses = $user->can('master.classes.view');
                        $canMasterCategories = $user->can('master.categories.view');
                        $canMasterUnits = $user->can('master.units.view');
                        $canMasterSuppliers = $user->can('master.suppliers.view');
                        $canMasterCustomers = $user->can('master.customers.view');
                        $canMasterShifts = $user->can('master.shifts.view');
                        $canMasterDivisions = $user->can('master.divisions.view');
                        $hasMasterMenu =
                            $canMasterClasses ||
                            $canMasterCategories ||
                            $canMasterUnits ||
                            $canMasterSuppliers ||
                            $canMasterCustomers ||
                            $canMasterShifts ||
                            $canMasterDivisions;

                        $canAppSettings = $user->can('settings.app.view');
                        $canDiscountSettings = $user->can('discounts.manage');
                        $canActivityLogs = $user->can('activity-logs.view');
                        $canSoundNotifications = $user->can('sound-notifications.view');
                        $canSystemLogs = $user->hasRole('Admin');
                        $settingsItems =
                            ($hasMasterMenu ? 1 : 0) +
                            ($canAppSettings ? 1 : 0) +
                            ($canDiscountSettings ? 1 : 0) +
                            ($canActivityLogs ? 1 : 0) +
                            ($canSoundNotifications ? 1 : 0) +
                            ($canSystemLogs ? 1 : 0) +
                            ($canGuides ? 1 : 0);
                    }
                @endphp

                @php
                    $sidebarDefaults = [
                        'userManagementItems' => 0,
                        'studentItems' => 0,
                        'inventoryItems' => 0,
                        'transactionItems' => 0,
                        'reportItems' => 0,
                        'settingsItems' => 0,
                        'canUsers' => false,
                        'canCategoryPermissions' => false,
                        'canRoles' => false,
                        'canPermissions' => false,
                        'canStudents' => false,
                        'canStudentGroups' => false,
                        'canStudentMembers' => false,
                        'canSchedules' => false,
                        'canGroupAttendances' => false,
                        'canGuides' => false,
                        'canGuideManage' => false,
                        'canMaterialGroup' => false,
                        'canProductGroup' => false,
                        'canRecipe' => false,
                        'canPurchases' => false,
                        'canProductions' => false,
                        'canProductionOrders' => false,
                        'canSales' => false,
                        'canReportSales' => false,
                        'canReportCustomers' => false,
                        'canReportPurchases' => false,
                        'canReportProductions' => false,
                        'canReportStocks' => false,
                        'canReportSchedules' => false,
                        'hasMasterMenu' => false,
                        'canMasterClasses' => false,
                        'canMasterCategories' => false,
                        'canMasterUnits' => false,
                        'canMasterSuppliers' => false,
                        'canMasterCustomers' => false,
                        'canMasterShifts' => false,
                        'canMasterDivisions' => false,
                        'canAppSettings' => false,
                        'canDiscountSettings' => false,
                        'canActivityLogs' => false,
                        'canSoundNotifications' => false,
                        'canSystemLogs' => false,
                    ];

                    foreach ($sidebarDefaults as $sidebarVar => $sidebarDefault) {
                        $$sidebarVar = $$sidebarVar ?? $sidebarDefault;
                    }
                @endphp

                <!-- ADMIN MENU -->
                @canany(['dashboard.view', 'users.manage', 'roles.view', 'roles.manage', 'permissions.view',
                    'permissions.manage', 'students.view', 'student-groups.view', 'student-group-members.view',
                    'schedules.view', 'materials.view', 'material-stocks.view', 'material-stock-logs.view', 'products.view',
                    'product-stocks.view', 'product-stock-logs.view', 'product-materials.view', 'material-wastes.view',
                    'product-wastes.view', 'purchases.view', 'productions.view', 'sales.view', 'reports.sales.view',
                    'production-orders.view',
                    'reports.purchases.view', 'reports.productions.view', 'reports.stocks.view', 'reports.schedules.view',
                    'master.categories.view', 'master.units.view', 'master.suppliers.view', 'master.customers.view',
                    'master.shifts.view', 'master.divisions.view', 'master.classes.view', 'discounts.manage',
                    'settings.app.view', 'activity-logs.view', 'sound-notifications.view'])
                    {{-- <li class="menu-title">
                        <span>Administrator</span>
                    </li> --}}
                @endcanany

                {{-- DASHBOARD --}}
                @can('dashboard.view')
                    <li>
                        <a wire:navigate href="{{ route('dashboard.index') }}"
                            class="{{ request()->routeIs('dashboard*') ? 'bg-base-300' : '' }}">
                            <x-heroicon-o-home class="w-5" />
                            Dashboard
                        </a>
                    </li>
                @endcan

                @can('sales.view')
                    {{-- KASIR MENU --}}
                    <li>
                        <a wire:navigate href="{{ route('kasir.pos') }}"
                            class="{{ request()->routeIs('kasir.pos') || request()->is('kasir/pos*') ? 'bg-base-300' : '' }}">
                            <x-heroicon-o-computer-desktop class="w-5" />
                            Kasir (POS)
                        </a>
                    </li>
                    @role('Cashier')
                        <li>
                            <a wire:navigate href="{{ route('sales.index') }}"
                                class="{{ request()->routeIs('sales.index') ? 'bg-base-300' : '' }}">
                                <x-heroicon-o-clock class="w-5" />
                                Riwayat Penjualan
                            </a>
                        </li>
                    @endrole
                @endcan
                {{-- @role('Cashier')
                    @can('reports.sales.view')
                        <li>
                            <a wire:navigate href="/reports/sales"
                                class="{{ request()->is('reports/sales*') ? 'bg-base-300' : '' }}">
                                <x-heroicon-o-chart-bar class="w-5" />
                                Laporan Penjualan
                            </a>
                        </li>
                    @endcan
                @endrole --}}

                {{-- MANAJEMEN PENGGUNA --}}
                @if (($userManagementItems ?? 0) > 0)
                    <li>
                        @if (($userManagementItems ?? 0) === 1)
                            @if ($canUsers)
                                <a wire:navigate href="{{ route('users.index') }}"
                                    class="{{ request()->routeIs('users.*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-users class="w-5" />
                                    Pengguna
                                </a>
                            @elseif($canCategoryPermissions)
                                <a wire:navigate href="{{ route('category-permissions.index') }}"
                                    class="{{ request()->routeIs('category-permissions.*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-shield-check class="w-5" />
                                    Kategori Permission
                                </a>
                            @elseif($canRoles)
                                <a wire:navigate href="{{ route('roles.index') }}"
                                    class="{{ request()->routeIs('roles.*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-shield-check class="w-5" />
                                    Role
                                </a>
                            @elseif($canPermissions)
                                <a wire:navigate href="{{ route('permissions.index') }}"
                                    class="{{ request()->routeIs('permissions.*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-shield-check class="w-5" />
                                    Permission
                                </a>
                            @endif
                        @else
                            <details
                                {{ request()->routeIs('users.*', 'roles.*', 'permissions.*', 'category-permissions.*') ? 'open' : '' }}>
                                <summary>
                                    <x-heroicon-o-users class="w-5" />
                                    Pengguna & Akses
                                </summary>
                                <ul>
                                    @if ($canUsers)
                                        <li>
                                            <a wire:navigate href="{{ route('users.index') }}"
                                                class="{{ request()->routeIs('users.*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-user-circle class="w-4 h-4" />
                                                Pengguna
                                            </a>
                                        </li>
                                    @endif

                                    @if ($canCategoryPermissions || $canRoles || $canPermissions)
                                        <li>
                                            <details
                                                {{ request()->routeIs('roles.*', 'permissions.*', 'category-permissions.*') ? 'open' : '' }}>
                                                <summary>
                                                    <x-heroicon-o-shield-check class="w-5" />
                                                    Role & Permission
                                                </summary>
                                                <ul>
                                                    @if ($canCategoryPermissions)
                                                        <li>
                                                            <a wire:navigate
                                                                href="{{ route('category-permissions.index') }}"
                                                                class="{{ request()->routeIs('category-permissions.*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                                                                Kategori Permission
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canRoles)
                                                        <li>
                                                            <a wire:navigate href="{{ route('roles.index') }}"
                                                                class="{{ request()->routeIs('roles.*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-shield-check class="w-4 h-4" />
                                                                Role
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canPermissions)
                                                        <li>
                                                            <a wire:navigate href="{{ route('permissions.index') }}"
                                                                class="{{ request()->routeIs('permissions.*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-key class="w-4 h-4" />
                                                                Permission
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </details>
                                        </li>
                                    @endif
                                </ul>
                            </details>
                        @endif
                    </li>
                @endif

                {{-- MANAJEMEN SISWA --}}
                @if (($studentItems ?? 0) > 0)
                    <li>
                        @if (($studentItems ?? 0) === 1)
                            @if ($canStudents)
                                <a wire:navigate href="/students"
                                    class="{{ request()->is('students*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-academic-cap class="w-5" />
                                    Data Siswa
                                </a>
                            @elseif($canStudentGroups)
                                <a wire:navigate href="/student-groups"
                                    class="{{ request()->is('student-groups*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-academic-cap class="w-5" />
                                    Kelompok Siswa
                                </a>
                            @elseif($canStudentMembers)
                                <a wire:navigate href="/student-group-members"
                                    class="{{ request()->is('student-group-members*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-academic-cap class="w-5" />
                                    Anggota Kelompok
                                </a>
                            @endif
                        @else
                            <details
                                {{ request()->is('students*', 'student-groups*', 'student-group-members*', 'master/classes*') ? 'open' : '' }}>
                                <summary>
                                    <x-heroicon-o-academic-cap class="w-5" />
                                    Siswa & Kelompok
                                </summary>
                                <ul>
                                    @if ($canStudents)
                                        <li>
                                            <a wire:navigate href="/students"
                                                class="{{ request()->is('students*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-academic-cap class="w-4 h-4" />
                                                Data Siswa
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canStudentGroups)
                                        <li>
                                            <a wire:navigate href="/student-groups"
                                                class="{{ request()->is('student-groups*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-user-group class="w-4 h-4" />
                                                Kelompok Siswa
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canStudentMembers)
                                        <li>
                                            <a wire:navigate href="/student-group-members"
                                                class="{{ request()->is('student-group-members*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-users class="w-4 h-4" />
                                                Anggota Kelompok
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </details>
                        @endif
                    </li>
                @endif

                {{-- PENJADWALAN --}}
                @if ($canSchedules || $canGroupAttendances)
                    <li>
                        <details {{ request()->is('schedules*', 'student-attendances*', 'student-group-attendances*') ? 'open' : '' }}>
                            <summary>
                                <x-heroicon-o-calendar class="w-5" />
                                Jadwal & Kehadiran
                            </summary>
                            <ul>
                                @if ($canSchedules)
                                    <li>
                                        <a wire:navigate href="/schedules"
                                            class="{{ request()->is('schedules*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                                            Jadwal Harian
                                        </a>
                                    </li>
                                    <li>
                                        <a wire:navigate href="/student-attendances"
                                            class="{{ request()->is('student-attendances*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                            <x-heroicon-o-check-badge class="w-4 h-4" />
                                            Kehadiran Siswa
                                        </a>
                                    </li>
                                @endif
                                @if ($canGroupAttendances)
                                    <li>
                                        <a wire:navigate href="/student-group-attendances"
                                            class="{{ request()->is('student-group-attendances*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                            <x-heroicon-o-user-group class="w-4 h-4" />
                                            Kehadiran Grup
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </details>
                    </li>
                @endif

                {{-- MANAJEMEN INVENTARIS --}}
                @if (($inventoryItems ?? 0) > 0)
                    <li>
                        @if (($inventoryItems ?? 0) === 1)
                            @if ($canRecipe)
                                <a wire:navigate href="/product-materials"
                                    class="{{ request()->is('product-materials*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-beaker class="w-5" />
                                    Resep Produk
                                </a>
                            @elseif($canMaterialGroup)
                                @php
                                    $inventoryMaterialRoute = $user?->can('material-stocks.view')
                                        ? '/material-stocks'
                                        : ($user?->can('materials.view')
                                            ? '/materials'
                                            : ($user?->can('material-stock-logs.view')
                                                ? '/material-stock-logs'
                                                : '/material-wastes'));
                                @endphp
                                <a wire:navigate href="{{ $inventoryMaterialRoute }}"
                                    class="{{ request()->is('materials*', 'material-stocks*', 'material-stock-logs*', 'material-wastes*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-archive-box class="w-5" />
                                    Inventaris & Stok
                                </a>
                            @elseif($canProductGroup)
                                @php
                                    $inventoryProductRoute = $user?->can('product-stocks.view')
                                        ? '/product-stocks'
                                        : ($user?->can('products.view')
                                            ? '/products'
                                            : ($user?->can('product-stock-logs.view')
                                                ? '/product-stock-logs'
                                                : '/product-wastes'));
                                @endphp
                                <a wire:navigate href="{{ $inventoryProductRoute }}"
                                    class="{{ request()->is('products*', 'product-stocks*', 'product-stock-logs*', 'product-wastes*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-archive-box class="w-5" />
                                    Inventaris & Stok
                                </a>
                            @endif
                        @else
                            <details
                                {{ request()->is('materials*', 'material-stocks*', 'material-stock-logs*', 'material-wastes*', 'products*', 'product-stocks*', 'product-stock-logs*', 'product-wastes*', 'product-materials*') ? 'open' : '' }}>
                                <summary>
                                    <x-heroicon-o-archive-box class="w-5" />
                                    Inventaris & Stok
                                </summary>
                                <ul>
                                    @if ($canMaterialGroup)
                                        <li>
                                            <details
                                                {{ request()->is('materials*', 'material-stocks*', 'material-stock-logs*', 'material-wastes*') ? 'open' : '' }}>
                                                <summary>
                                                    <x-heroicon-o-cube class="w-5" />
                                                    Bahan Baku
                                                </summary>
                                                <ul>
                                                    @can('materials.view')
                                                        <li>
                                                            <a wire:navigate href="/materials"
                                                                class="{{ request()->is('materials*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-cube class="w-4 h-4" />
                                                                Data Material
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('material-stocks.view')
                                                        <li>
                                                            <a wire:navigate href="/material-stocks"
                                                                class="{{ request()->is('material-stocks*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-archive-box class="w-4 h-4" />
                                                                Stok Material
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('material-stock-logs.view')
                                                        <li>
                                                            <a wire:navigate href="/material-stock-logs"
                                                                class="{{ request()->is('material-stock-logs*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-clock class="w-4 h-4" />
                                                                Riwayat Stok Material
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('material-wastes.view')
                                                        <li>
                                                            <a wire:navigate href="/material-wastes"
                                                                class="{{ request()->is('material-wastes*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                                Kadaluarsa / Gagal Bahan
                                                            </a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </details>
                                        </li>
                                    @endif
                                    @if ($canProductGroup)
                                        <li>
                                            <details
                                                {{ request()->is('products*', 'product-stocks*', 'product-stock-logs*', 'product-wastes*') ? 'open' : '' }}>
                                                <summary>
                                                    <x-heroicon-o-shopping-bag class="w-5" />
                                                    Produk Jadi
                                                </summary>
                                                <ul>
                                                    @can('products.view')
                                                        <li>
                                                            <a wire:navigate href="/products"
                                                                class="{{ request()->is('products*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-shopping-bag class="w-4 h-4" />
                                                                Data Produk
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('product-stocks.view')
                                                        <li>
                                                            <a wire:navigate href="/product-stocks"
                                                                class="{{ request()->is('product-stocks*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-archive-box class="w-4 h-4" />
                                                                Stok Produk
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('product-stock-logs.view')
                                                        <li>
                                                            <a wire:navigate href="/product-stock-logs"
                                                                class="{{ request()->is('product-stock-logs*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-clock class="w-4 h-4" />
                                                                Riwayat Stok Produk
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('product-wastes.view')
                                                        <li>
                                                            <a wire:navigate href="/product-wastes"
                                                                class="{{ request()->is('product-wastes*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                                Kadaluarsa / Gagal Produk
                                                            </a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </details>
                                        </li>
                                    @endif
                                    @if ($canRecipe)
                                        <li>
                                            <a wire:navigate href="/product-materials"
                                                class="{{ request()->is('product-materials*') ? 'bg-base-300' : '' }}">
                                                <x-heroicon-o-beaker class="w-5" />
                                                Resep & Formula
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </details>
                        @endif
                    </li>
                @endif

                {{-- MANAJEMEN TRANSAKSI (Non-Cashier) --}}
                @if (($transactionItems ?? 0) > 0)
                    <li>
                        @if (($transactionItems ?? 0) === 1)
                            @if ($canPurchases)
                                <a wire:navigate href="/purchases"
                                    class="{{ request()->is('purchases*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-banknotes class="w-5" />
                                    Pembelian
                                </a>
                            @elseif($canProductions)
                                <a wire:navigate href="/productions"
                                    class="{{ request()->is('productions*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-banknotes class="w-5" />
                                    Produksi
                                </a>
                            @elseif($canProductionOrders)
                                <a wire:navigate href="{{ route('production.orders.index') }}"
                                    class="{{ request()->routeIs('production.orders.*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-list-bullet class="w-5" />
                                    Antrian Pesanan
                                </a>
                            @elseif($canSales)
                                <a wire:navigate href="/sales"
                                    class="{{ request()->is('sales*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-banknotes class="w-5" />
                                    Penjualan
                                </a>
                            @endif
                        @else
                            <details {{ request()->is('purchases*', 'productions*', 'sales*') || request()->routeIs('production.orders.*') ? 'open' : '' }}>
                                <summary>
                                    <x-heroicon-o-banknotes class="w-5" />
                                    Transaksi Operasional
                                </summary>
                                <ul>
                                    @if ($canPurchases)
                                        <li>
                                            <a wire:navigate href="/purchases"
                                                class="{{ request()->is('purchases*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-banknotes class="w-4 h-4" />
                                                Pembelian
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canProductions)
                                        <li>
                                            <a wire:navigate href="/productions"
                                                class="{{ request()->is('productions*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-wrench-screwdriver class="w-4 h-4" />
                                                Produksi
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canProductionOrders)
                                        <li>
                                            <a wire:navigate href="{{ route('production.orders.index') }}"
                                                class="{{ request()->routeIs('production.orders.*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-list-bullet class="w-4 h-4" />
                                                Antrian Pesanan
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canSales)
                                        <li>
                                            <a wire:navigate href="/sales"
                                                class="{{ request()->is('sales*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-receipt-percent class="w-4 h-4" />
                                                Penjualan
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </details>
                        @endif
                    </li>
                @endif

                {{-- LAPORAN & ANALITIK --}}
                @if (($reportItems ?? 0) > 0)
                    <li>
                        @if (($reportItems ?? 0) === 1)
                            @if ($canReportSales)
                                <a wire:navigate href="/reports/sales"
                                    class="{{ request()->is('reports/sales*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-chart-bar class="w-5" />
                                    Laporan Penjualan
                                </a>
                            @elseif($canReportPurchases)
                                <a wire:navigate href="/reports/purchases"
                                    class="{{ request()->is('reports/purchases*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-chart-bar class="w-5" />
                                    Laporan Pembelian
                                </a>
                            @elseif($canReportProductions)
                                <a wire:navigate href="/reports/productions"
                                    class="{{ request()->is('reports/productions*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-chart-bar class="w-5" />
                                    Laporan Produksi
                                </a>
                            @elseif($canReportStocks)
                                <a wire:navigate href="/reports/stocks"
                                    class="{{ request()->is('reports/stocks*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-chart-bar class="w-5" />
                                    Laporan Stok
                                </a>
                            @elseif($canReportSchedules)
                                <a wire:navigate href="/reports/schedules"
                                    class="{{ request()->is('reports/schedules*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-chart-bar class="w-5" />
                                    Laporan Jadwal
                                </a>
                            @endif
                        @else
                            <details {{ request()->is('reports*') ? 'open' : '' }}>
                                <summary>
                                    <x-heroicon-o-chart-bar class="w-5" />
                                    Laporan & Analisis
                                </summary>
                                <ul>
                                    @if ($canReportSales)
                                        <li>
                                            <a wire:navigate href="/reports/sales"
                                                class="{{ request()->is('reports/sales*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-chart-bar class="w-4 h-4" />
                                                Laporan Penjualan
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canReportCustomers)
                                        <li>
                                            <a wire:navigate href="/reports/customers"
                                                class="{{ request()->is('reports/customers*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-user-group class="w-4 h-4" />
                                                Laporan Pelanggan
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canReportPurchases)
                                        <li>
                                            <a wire:navigate href="/reports/purchases"
                                                class="{{ request()->is('reports/purchases*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-chart-bar class="w-4 h-4" />
                                                Laporan Pembelian
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canReportProductions)
                                        <li>
                                            <a wire:navigate href="/reports/productions"
                                                class="{{ request()->is('reports/productions*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-chart-bar class="w-4 h-4" />
                                                Laporan Produksi
                                            </a>
                                        </li>
                                        <li>
                                            <a wire:navigate href="/reports/wastes"
                                                class="{{ request()->is('reports/wastes*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                                                Laporan Kadaluarsa / Gagal
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canReportStocks)
                                        <li>
                                            <a wire:navigate href="/reports/stocks"
                                                class="{{ request()->is('reports/stocks*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-chart-bar class="w-4 h-4" />
                                                Laporan Stok
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canReportSchedules)
                                        <li>
                                            <a wire:navigate href="/reports/schedules"
                                                class="{{ request()->is('reports/schedules*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-chart-bar class="w-4 h-4" />
                                                Laporan Jadwal
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </details>
                        @endif
                    </li>
                @endif


                {{-- PENGATURAN --}}
                @if (($settingsItems ?? 0) > 0)
                    <li>
                        @if (($settingsItems ?? 0) === 1)
                            @if ($hasMasterMenu)
                                @php
                                    $masterRoute = $canMasterClasses
                                        ? '/master/classes'
                                        : ($canMasterCategories
                                            ? '/master/categories'
                                            : ($canMasterUnits
                                                ? '/master/units'
                                                : ($canMasterSuppliers
                                                    ? '/master/suppliers'
                                                    : ($canMasterCustomers
                                                        ? '/master/customers'
                                                        : ($canMasterShifts
                                                            ? '/master/shifts'
                                                            : '/master/divisions')))));
                                @endphp
                                <a wire:navigate href="{{ $masterRoute }}"
                                    class="{{ request()->is('master*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-cog-6-tooth class="w-5" />
                                    Pengaturan Sistem
                                </a>
                            @elseif($canAppSettings)
                                <a wire:navigate href="/settings/app"
                                    class="{{ request()->is('settings/app*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-cog-6-tooth class="w-5" />
                                    Pengaturan Aplikasi
                                </a>
                            @elseif($canDiscountSettings)
                                <a wire:navigate href="/settings/discounts"
                                    class="{{ request()->is('settings/discounts*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-cog-6-tooth class="w-5" />
                                    Set Diskon
                                </a>
                            @elseif($canActivityLogs)
                                <a wire:navigate href="{{ route('settings.activity-logs.index') }}"
                                    class="{{ request()->routeIs('settings.activity-logs.*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-cog-6-tooth class="w-5" />
                                    Log Aktivitas
                                </a>
                            @elseif($canSoundNotifications)
                                <a wire:navigate href="{{ route('settings.sound-notifications.index') }}"
                                    class="{{ request()->routeIs('settings.sound-notifications.*') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-speaker-wave class="w-5" />
                                    Notifikasi Suara
                                </a>
                            @elseif($canSystemLogs)
                                <a wire:navigate href="{{ route('laravel-logs') }}"
                                    class="{{ request()->routeIs('laravel-logs') ? 'bg-base-300' : '' }}">
                                    <x-heroicon-o-cog-6-tooth class="w-5" />
                                    Sistem Logs
                                </a>
                            @endif
                        @else
                            <details {{ request()->is('settings*', 'master*') || request()->routeIs('laravel-logs') ? 'open' : '' }}>
                                <summary>
                                    <x-heroicon-o-cog-6-tooth class="w-5" />
                                    Pengaturan Sistem
                                </summary>
                                <ul>
                                    @if ($hasMasterMenu)
                                        <li>
                                            <details
                                                {{ request()->is('master/categories*', 'master/units*', 'master/suppliers*', 'master/customers*', 'master/shifts*', 'master/divisions*', 'master/classes*') ? 'open' : '' }}>
                                                <summary>
                                                    <x-heroicon-o-squares-2x2 class="w-5" />
                                                    Master Data
                                                </summary>
                                                <ul>
                                                    @if ($canMasterClasses)
                                                        <li>
                                                            <a wire:navigate href="/master/classes"
                                                                class="{{ request()->is('master/classes*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-academic-cap class="w-4 h-4" />
                                                                Kelas
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canMasterCategories)
                                                        <li>
                                                            <a wire:navigate href="/master/categories"
                                                                class="{{ request()->is('master/categories*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-tag class="w-4 h-4" />
                                                                Kategori
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canMasterUnits)
                                                        <li>
                                                            <a wire:navigate href="/master/units"
                                                                class="{{ request()->is('master/units*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-scale class="w-4 h-4" />
                                                                Satuan
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canMasterSuppliers)
                                                        <li>
                                                            <a wire:navigate href="/master/suppliers"
                                                                class="{{ request()->is('master/suppliers*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-truck class="w-4 h-4" />
                                                                Supplier
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canMasterCustomers)
                                                        <li>
                                                            <a wire:navigate href="/master/customers"
                                                                class="{{ request()->is('master/customers*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-user-group class="w-4 h-4" />
                                                                Pelanggan
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canMasterShifts)
                                                        <li>
                                                            <a wire:navigate href="/master/shifts"
                                                                class="{{ request()->is('master/shifts*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-clock class="w-4 h-4" />
                                                                Shift
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canMasterDivisions)
                                                        <li>
                                                            <a wire:navigate href="/master/divisions"
                                                                class="{{ request()->is('master/divisions*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-building-office-2 class="w-4 h-4" />
                                                                Divisi
                                                            </a>
                                                        </li>
                                                    @endif

                                                </ul>
                                            </details>
                                        </li>
                                    @endif
                                    @if ($canAppSettings)
                                        <li>
                                            <a wire:navigate href="/settings/app"
                                                class="{{ request()->is('settings/app*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                                                Pengaturan Aplikasi
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canDiscountSettings)
                                        <li>
                                            <a wire:navigate href="/settings/discounts"
                                                class="{{ request()->is('settings/discounts*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                <x-heroicon-o-tag class="w-4 h-4" />
                                                Set Diskon
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canActivityLogs)
                                        <li>
                                            <a wire:navigate href="{{ route('settings.activity-logs.index') }}"
                                                class="{{ request()->routeIs('settings.activity-logs.*') ? 'bg-base-300' : '' }}">
                                                <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                                                Log Aktivitas
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canSoundNotifications)
                                        <li>
                                            <a wire:navigate href="{{ route('settings.sound-notifications.index') }}"
                                                class="{{ request()->routeIs('settings.sound-notifications.*') ? 'bg-base-300' : '' }}">
                                                <x-heroicon-o-speaker-wave class="w-4 h-4" />
                                                Notifikasi Suara
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canSystemLogs)
                                        <li>
                                            <a wire:navigate href="{{ route('laravel-logs') }}"
                                                class="{{ request()->routeIs('laravel-logs') ? 'bg-base-300' : '' }}">
                                                <x-heroicon-o-command-line class="w-4 h-4" />
                                                Sistem Logs
                                            </a>
                                        </li>
                                    @endif
                                    @if ($canGuides)
                                        <li>
                                            <details {{ request()->routeIs('guides.*') ? 'open' : '' }}>
                                                <summary>
                                                    <x-heroicon-o-book-open class="w-5" />
                                                    Panduan
                                                </summary>
                                                <ul>
                                                    <li>
                                                        <a wire:navigate href="{{ route('guides.index') }}"
                                                            class="{{ request()->routeIs('guides.index') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                            <x-heroicon-o-book-open class="w-4 h-4" />
                                                            Pusat Bantuan
                                                        </a>
                                                    </li>
                                                    @if ($canGuideManage)
                                                        <li>
                                                            <a wire:navigate href="{{ route('guides.articles.index') }}"
                                                                class="{{ request()->routeIs('guides.articles.*') ? 'bg-base-300' : '' }} flex items-center gap-2">
                                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                                Kelola Konten Panduan
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </details>
                                        </li>
                                    @endif
                                </ul>
                            </details>
                        @endif
                    </li>
                @endif
            </ul>
        </div>

        <!-- Sidebar Footer -->
        {{-- <div class="absolute bottom-4 left-4 right-4">
            <div class="stats shadow bg-base-200">
                <div class="stat p-3">
                    <div class="stat-title text-xs">Status Sistem</div>
                    <div class="stat-value text-sm text-success">Online</div>
                    <div class="stat-desc text-xs">Last sync: {{ now()->format('H:i') }}</div>
                </div>
            </div>
        </div> --}}
    </div>
</aside>

