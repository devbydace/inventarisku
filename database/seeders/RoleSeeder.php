<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'kasir', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'audit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manager', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'staff_toko', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('roles')->insert($roles);
    }
}