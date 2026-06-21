<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Pieces', 'abbreviation' => 'pcs'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Liter', 'abbreviation' => 'lt'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Pack', 'abbreviation' => 'pack'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}