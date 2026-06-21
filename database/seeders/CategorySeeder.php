<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik'],
            ['name' => 'Alat Tulis'],
            ['name' => 'Perlengkapan Kantor'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}