<?php

namespace Database\Seeders;

use App\Models\Pack;
use App\Models\PackingList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plGroups = PackingList::all()->groupBy('product_id');

        foreach ($plGroups as $productId => $items) {
            // Buat satu pack per product_id
            $pack = Pack::create([
                'product_id'    => $productId,
                'name'          => 'Default',
                'vendor_id'     => null,
            ]);

            // Tambahkan item-item ke pack
            $pack->items()->createMany(
                $items->map(function ($item) {
                    return [
                        'item' => $item->item,
                        'qty'  => $item->qty,
                    ];
                })->toArray()
            );
        }
    }
}
