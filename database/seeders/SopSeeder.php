<?php

namespace Database\Seeders;

use App\Models\Sop;
use App\Models\Target;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targ = Target::with('items')->get();
        foreach ($targ as $item) {
            $sop = Sop::create([
                'product_id' => $item->product_id,
                'target'     => $item->target,
            ]);

            // Jika Target punya items, buat SOP items-nya
            if ($item->items && $item->items->count() > 0) {
                $sop->items()->createMany(
                    $item->items->map(function ($x) {
                        return [
                            'item' => $x->item,
                        ];
                    })->toArray()
                );
            }
        }
    }
}
