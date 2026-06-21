<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyProfile;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyProfile::create([
            'company_name' => 'PT. Inventaris Ku',
            'address' => 'Jl. Contoh No. 123, Jakarta',
            'contact' => 'Telp: (021) 123-4567',
            'date_format' => 'Y-m-d',
            'currency' => 'IDR',
        ]);
    }
}