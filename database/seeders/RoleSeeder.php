<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Note: This seeder is kept for backward compatibility.
     * PermissionSeeder is the primary seeder for roles and permissions.
     */
    public function run(): void
    {
        $roles = ['admin', 'kasir', 'audit', 'manager', 'staff_toko'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}