<?php

namespace Database\Seeders;

use App\Models\Kontak;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $c = Kontak::all()->pluck('name')->toArray();
        $data = collect($c)->map(function ($name) {
            return ['name' => $name];
        })->toArray();
        Vendor::insert($data);
    }
}
