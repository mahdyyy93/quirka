<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'Products' => [
                'products.view',
                'products.view-all',
                'products.create',
                'products.edit',
                'products.delete',
                'products.toggle-availability',
            ],
            'Orders' => [
                'orders.view',
                'orders.view-all',
                'orders.update-status',
                'orders.verify-cod',
                'orders.verify-instapay',
            ],
            'Customers' => [
                'customers.view',
            ],
            'Roles & Permissions' => [
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
                'roles.assign-permissions',
                'permissions.create',
                'permissions.delete',
                'users.assign-role',
            ],
        ];

        foreach ($modules as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web'],
                    ['group_name' => $group]
                );
            }
        }

        // Clean up obsolete supplier permissions if they exist
        Permission::where('name', 'like', 'suppliers.%')->delete();

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerPermissions = Permission::whereIn('name', [
            'products.view',
            'products.view-all',
            'products.create',
            'products.edit',
            'products.toggle-availability',
            'orders.view',
            'orders.view-all',
            'orders.update-status',
            'customers.view',
            'roles.view',
        ])->get();
        $managerRole->syncPermissions($managerPermissions);

        // Artist Role: Can view and manage only their own products, and view only their orders (read-only)
        $artistRole = Role::firstOrCreate(['name' => 'artist', 'guard_name' => 'web']);
        $artistPermissions = Permission::whereIn('name', [
            'products.view',
            'products.create',
            'products.edit',
            'orders.view',
        ])->get();
        $artistRole->syncPermissions($artistPermissions);

        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Sync existing users
        User::where('role', 'admin')->each(function (User $user) use ($adminRole) {
            $user->updateQuietly(['role_id' => $adminRole->id, 'role' => 'admin']);
            $user->syncRoles([$adminRole]);
        });

        User::where(function ($query) {
            $query->where('role', 'customer')->orWhereNull('role_id');
        })->where('role', '!=', 'admin')->each(function (User $user) use ($customerRole) {
            $user->updateQuietly(['role_id' => $customerRole->id, 'role' => 'customer']);
            $user->syncRoles([$customerRole]);
        });
    }
}
