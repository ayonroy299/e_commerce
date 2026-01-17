<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage products',
            'manage inventory', // adjustments, grn
            'manage suppliers',
            'manage customers',
            'access pos',
            'view financials', // reports, expenses
            'manage settings',
            'manage roles', // Only super-admin usually
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Define Roles and Assign Permissions

        // Super Admin (Implicitly has all permissions via Gate::before or just assign all)
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        // Usually handles via Gate::before in AuthServiceProvider, but for clarity we can sync all
        $superAdmin->syncPermissions(Permission::all());

        // Admin (Branch Admin)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view dashboard',
            'manage users',
            'manage products',
            'manage inventory',
            'manage suppliers',
            'manage customers',
            'access pos',
            'view financials',
            'manage settings',
        ]);

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view dashboard',
            'manage products',
            'manage inventory',
            'manage suppliers',
            'manage customers',
            'access pos',
        ]);

        // Cashier
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->syncPermissions([
            'access pos',
            'manage customers', // Create walk-ins
        ]);

        // Staff (Restricted)
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'view dashboard',
        ]);
        
        // Assign Super Admin role to the first user if exists
        $user = \App\Models\User::first();
        if ($user) {
            $user->assignRole('super-admin');
        }
    }
}
