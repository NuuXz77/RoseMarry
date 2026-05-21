<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $dashboardCategory = CategoryPermissions::where('name', 'Dashboard')->first();
        $userCategory = CategoryPermissions::where('name', 'Manajemen Pengguna')->first();
        $studentCategory = CategoryPermissions::where('name', 'Manajemen Siswa')->first();
        $scheduleCategory = CategoryPermissions::where('name', 'Penjadwalan')->first();
        $inventoryCategory = CategoryPermissions::where('name', 'Manajemen Inventaris')->first();
        $transactionCategory = CategoryPermissions::where('name', 'Manajemen Transaksi')->first();
        $reportCategory = CategoryPermissions::where('name', 'Laporan & Analitik')->first();
        $settingCategory = CategoryPermissions::where('name', 'Pengaturan')->first();

        $permissions = [
            // Sidebar permissions
            ['name' => 'dashboard.view', 'category_id' => optional($dashboardCategory)->id],
            ['name' => 'users.manage', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.view', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.manage', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.view', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.manage', 'category_id' => optional($userCategory)->id],

            // CRUD permissions used in components
            ['name' => 'users.view', 'category_id' => optional($userCategory)->id],
            ['name' => 'users.create', 'category_id' => optional($userCategory)->id],
            ['name' => 'users.edit', 'category_id' => optional($userCategory)->id],
            ['name' => 'users.delete', 'category_id' => optional($userCategory)->id],

            ['name' => 'roles.create', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.edit', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.delete', 'category_id' => optional($userCategory)->id],

            ['name' => 'permissions.create', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.edit', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.delete', 'category_id' => optional($userCategory)->id],

            // Manajemen Siswa
            ['name' => 'students.view', 'category_id' => optional($studentCategory)->id],
            ['name' => 'students.create', 'category_id' => optional($studentCategory)->id],
            ['name' => 'students.edit', 'category_id' => optional($studentCategory)->id],
            ['name' => 'students.delete', 'category_id' => optional($studentCategory)->id],
            ['name' => 'students.manage', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-groups.view', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-groups.create', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-groups.edit', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-groups.delete', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-groups.manage', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-group-members.view', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-group-members.create', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-group-members.edit', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-group-members.delete', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-group-members.manage', 'category_id' => optional($studentCategory)->id],

            // Penjadwalan
            ['name' => 'schedules.view', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'schedules.create', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'schedules.edit', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'schedules.delete', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'schedules.manage', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'student-group-attendances.view', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'student-group-attendances.create', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'student-group-attendances.edit', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'student-group-attendances.delete', 'category_id' => optional($scheduleCategory)->id],
            ['name' => 'student-group-attendances.manage', 'category_id' => optional($scheduleCategory)->id],

            // Manajemen Inventaris
            ['name' => 'materials.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'materials.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'materials.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'materials.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'materials.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stocks.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stocks.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stocks.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stocks.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stocks.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stock-logs.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stock-logs.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stock-logs.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stock-logs.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stock-logs.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'products.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'products.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'products.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'products.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'products.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stocks.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stocks.adjust', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stocks.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stocks.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stocks.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stocks.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stock-logs.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stock-logs.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stock-logs.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stock-logs.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stock-logs.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-materials.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-materials.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-materials.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-materials.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-materials.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-wastes.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-wastes.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-wastes.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-wastes.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-wastes.manage', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-wastes.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-wastes.create', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-wastes.edit', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-wastes.delete', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-wastes.manage', 'category_id' => optional($inventoryCategory)->id],

            // Manajemen Transaksi
            ['name' => 'purchases.view', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'purchases.create', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'purchases.edit', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'purchases.delete', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'purchases.manage', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'productions.view', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'productions.create', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'productions.edit', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'productions.delete', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'productions.manage', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'production-orders.view', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'production-orders.manage', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'production-orders.call', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'sales.view', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'sales.create', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'sales.edit', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'sales.delete', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'sales.manage', 'category_id' => optional($transactionCategory)->id],

            // POS Shortcuts (Admin only)
            ['name' => 'pos.quick-add-product', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'pos.quick-adjust-stock', 'category_id' => optional($transactionCategory)->id],

            // Laporan & Analitik
            ['name' => 'reports.sales.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.customers.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.purchases.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.productions.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.stocks.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.schedules.view', 'category_id' => optional($reportCategory)->id],

            // Master Data (Pengaturan)
            ['name' => 'master.categories.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.categories.create', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.categories.edit', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.categories.delete', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.categories.manage', 'category_id' => optional($settingCategory)->id],

            ['name' => 'master.units.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.units.create', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.units.edit', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.units.delete', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.units.manage', 'category_id' => optional($settingCategory)->id],

            ['name' => 'master.suppliers.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.suppliers.create', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.suppliers.edit', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.suppliers.delete', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.suppliers.manage', 'category_id' => optional($settingCategory)->id],

            ['name' => 'master.customers.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.customers.create', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.customers.edit', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.customers.delete', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.customers.manage', 'category_id' => optional($settingCategory)->id],

            ['name' => 'master.shifts.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.shifts.create', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.shifts.edit', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.shifts.delete', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.shifts.manage', 'category_id' => optional($settingCategory)->id],

            ['name' => 'master.divisions.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.divisions.create', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.divisions.edit', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.divisions.delete', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.divisions.manage', 'category_id' => optional($settingCategory)->id],

            ['name' => 'master.classes.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.classes.create', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.classes.edit', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.classes.delete', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.classes.manage', 'category_id' => optional($settingCategory)->id],

            ['name' => 'settings.app.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'settings.app.manage', 'category_id' => optional($settingCategory)->id],
            ['name' => 'activity-logs.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'sound-notifications.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'sound-notifications.manage', 'category_id' => optional($settingCategory)->id],
            ['name' => 'discounts.manage', 'category_id' => optional($settingCategory)->id],
            ['name' => 'guides.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'guides.manage', 'category_id' => optional($settingCategory)->id],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                [
                    'category_id' => $permission['category_id'],
                    'description' => $this->makePermissionDescription($permission['name']),
                ]
            );
        }
    }

    private function makePermissionDescription(string $slug): string
    {
        $entityLabels = [
            'dashboard' => 'Dashboard',
            'users' => 'Data Pengguna',
            'roles' => 'Data Role',
            'permissions' => 'Data Permission',
            'students' => 'Data Siswa',
            'student-groups' => 'Kelompok Siswa',
            'student-group-members' => 'Anggota Kelompok Siswa',
            'schedules' => 'Jadwal',
            'student-group-attendances' => 'Kehadiran Grup',
            'materials' => 'Data Material',
            'material-stocks' => 'Stok Material',
            'material-stock-logs' => 'Riwayat Stok Material',
            'material-wastes' => 'Limbah Material',
            'products' => 'Data Produk',
            'product-stocks' => 'Stok Produk',
            'product-stock-logs' => 'Riwayat Stok Produk',
            'product-materials' => 'Resep Produk',
            'product-wastes' => 'Limbah Produk',
            'purchases' => 'Data Pembelian',
            'productions' => 'Data Produksi',
            'production-orders' => 'Antrian Pesanan',
            'sales' => 'Data Penjualan',
            'pos' => 'POS Shortcut',
            'reports.sales' => 'Laporan Penjualan',
            'reports.purchases' => 'Laporan Pembelian',
            'reports.productions' => 'Laporan Produksi',
            'reports.stocks' => 'Laporan Stok',
            'reports.customers' => 'Laporan Pelanggan',
            'reports.schedules' => 'Laporan Jadwal',
            'master.categories' => 'Master Kategori',
            'master.units' => 'Master Satuan',
            'master.suppliers' => 'Master Supplier',
            'master.customers' => 'Master Customer',
            'master.shifts' => 'Master Shift',
            'master.divisions' => 'Master Divisi',
            'master.classes' => 'Master Kelas',
            'settings.app' => 'Pengaturan Aplikasi',
            'activity-logs' => 'Log Aktivitas',
            'sound-notifications' => 'Notifikasi Suara',
            'discounts' => 'Pengaturan Diskon',
            'guides' => 'Pusat Panduan',
        ];

        $actionLabels = [
            'view' => 'Lihat',
            'adjust' => 'Sesuaikan',
            'create' => 'Tambah',
            'edit' => 'Edit',
            'delete' => 'Hapus',
            'manage' => 'Kelola',
            'call' => 'Panggil',
            'quick-add-product' => 'Quick Add Produk',
            'quick-adjust-stock' => 'Quick Adjust Stok',
        ];

        $lastDotPosition = strrpos($slug, '.');

        if ($lastDotPosition === false) {
            return ucwords(str_replace(['-', '.'], ' ', $slug));
        }

        $entity = substr($slug, 0, $lastDotPosition);
        $action = substr($slug, $lastDotPosition + 1);

        $entityLabel = $entityLabels[$entity] ?? ucwords(str_replace(['-', '.'], ' ', $entity));
        $actionLabel = $actionLabels[$action] ?? ucwords(str_replace(['-', '.'], ' ', $action));

        return trim($actionLabel . ' ' . $entityLabel);
    }
}
