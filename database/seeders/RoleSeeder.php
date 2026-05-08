<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'Admin',
            'Production',
            'Inventory',
            'Cashier',
            // 'Supervisor',
            // 'QC',
        ];

        $rolePermissionMap = [
            'Production' => [
                'dashboard.view',
                'productions.view',
                'production-orders.view',
                'production-orders.manage',

                'products.view',
                'products.create',
                'products.edit',
                'products.delete',
                'products.manage',

                'product-stocks.view',
                'product-stocks.create',
                'product-stocks.edit',
                'product-stocks.delete',
                'product-stocks.manage',

                'product-stock-logs.view',
                'product-stock-logs.manage',
                
                'product-materials.view',
                'product-wastes.view',
                'student-group-attendances.view',
                'reports.productions.view',
                'sound-notifications.view',
                'guides.view',
            ],

            'Inventory' => [
                'dashboard.view',

                'materials.view',
                'materials.create',
                'materials.edit',
                'materials.delete',
                'materials.manage',

                'material-stocks.view',
                'material-stocks.create',
                'material-stocks.edit',
                'material-stocks.delete',
                'material-stocks.manage',

                'material-stock-logs.view',
                'material-stock-logs.manage',

                'material-wastes.view',
                'material-wastes.create',
                'material-wastes.edit',
                'material-wastes.delete',
                'material-wastes.manage',

                'products.view',
                'products.create',
                'products.edit',
                'products.delete',
                'products.manage',

                'purchases.view',
                'purchases.create',
                'purchases.manage',

                'product-stocks.view',
                'product-stocks.create',
                'product-stocks.edit',
                'product-stocks.delete',
                'product-stocks.manage',

                'product-stock-logs.view',
                'product-stock-logs.manage',

                'product-materials.view',
                'product-materials.create',
                'product-materials.edit',
                'product-materials.delete',
                'product-materials.manage',

                'product-wastes.view',
                'product-wastes.create',
                'product-wastes.edit',
                'product-wastes.delete',
                'product-wastes.manage',
                'reports.stocks.view',
                'guides.view',
            ],

            'Cashier' => [
                'dashboard.view',
                'sales.view',
                'production-orders.view',
                'production-orders.call',
                'reports.sales.view',
                'sound-notifications.view',
                'guides.view',
            ],

            // 'Supervisor' => [
            //     'dashboard.view',
            //     'schedules.view',
            //     'schedules.manage',
            //     'purchases.view',
            //     'productions.view',
            //     'sales.view',
            //     'reports.sales.view',
            //     'reports.purchases.view',
            //     'reports.productions.view',
            //     'reports.stocks.view',
            //     'reports.schedules.view',
            // ],

            // 'QC' => [
            //     'dashboard.view',
            //     'productions.view',
            //     'products.view',
            //     'product-stocks.view',
            //     'material-wastes.view',
            //     'product-wastes.view',
            //     'reports.productions.view',
            //     'reports.stocks.view',
            // ],
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        /** @var Role $adminRole */
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::pluck('name')->toArray());
        }

        foreach ($rolePermissionMap as $roleName => $permissions) {
            /** @var Role|null $role */
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($permissions);
            }
        }

        // Explicitly lock admin-only permissions
        $adminOnlyPermissions = [
            'product-stocks.adjust',
            'pos.quick-add-product',
            'pos.quick-adjust-stock',
        ];
        foreach (['Production', 'Inventory', 'Cashier'] as $roleName) {
            /** @var Role|null $role */
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($adminOnlyPermissions as $adminPerm) {
                    if ($role->hasPermissionTo($adminPerm)) {
                        $role->revokePermissionTo($adminPerm);
                    }
                }
            }
        }
    }
}
