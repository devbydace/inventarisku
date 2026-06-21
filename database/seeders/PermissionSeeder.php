<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * All permissions in the system.
     */
    private const PERMISSIONS = [
        'view-dashboard',
        'manage-products',
        'manage-categories',
        'manage-suppliers',
        'manage-units',
        'create-stock-in',
        'create-stock-out',
        'create-stock-adjustment',
        'approve-transaction',
        'manage-users',
        'manage-settings',
        'view-audit-trail',
        'view-reports',
    ];

    /**
     * Role-permission mapping.
     */
    private const ROLE_PERMISSIONS = [
        'admin' => [
            'view-dashboard',
            'manage-products',
            'manage-categories',
            'manage-suppliers',
            'manage-units',
            'create-stock-in',
            'create-stock-out',
            'create-stock-adjustment',
            'approve-transaction',
            'manage-users',
            'manage-settings',
            'view-audit-trail',
            'view-reports',
        ],
        'manager' => [
            'view-dashboard',
            'approve-transaction',
            'view-audit-trail',
            'view-reports',
            'create-stock-adjustment',
        ],
        'audit' => [
            'view-dashboard',
            'approve-transaction',
            'view-audit-trail',
            'view-reports',
            'create-stock-adjustment',
        ],
        'kasir' => [
            'view-dashboard',
            'create-stock-in',
            'create-stock-out',
            'view-reports',
        ],
        'staff_toko' => [
            'view-dashboard',
            'create-stock-in',
            'create-stock-out',
            'view-reports',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }
    }
}