<?php

namespace Database\Seeders;

use App\Models\Alamat;
use App\Models\DetailAlamat;
use Illuminate\Database\Seeder;

class AlamatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $al = Alamat::create([
            'tujuan'    => 'Gudang Farmasi Dinkes Kab Asahan Dinkes Kab Balangan',
            'alamat'    => 'Jln. Durian No.8 Kel. Kisaran Naga Kec. Kisaran Timur, \nKisaran Kabupaten Asahan \nSumatera Utara Asahan',
            'ekspedisi' => 'TIKI DARAT/TRC',
            'koli'      => 1,
            'up'        => 'Ibu Yushidar',
            'tlp'       => '0811-628-189',
            'do'        => 'CENT/OUT/08267',
            'epur'      => 'AK1-P2306-5466671',
            'untuk'     => 'Dinkes Kab Slawi',
            'nilai'     => '45.353.369',
            'is_do'     => 'yes',
            'is_pk'     => 'no',
        ]);

        for ($i = 0; $i < 3; $i++) {
            DetailAlamat::create([
                'product_id'    => 768,
                'alamat_id'     => $al->id,
                'qty'           => 300,
                'lot'           => 'F2E2 / Ed. 2025.01',
            ]);
        }
    }
}
