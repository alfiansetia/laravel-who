<?php

namespace Database\Seeders;

use App\Models\Problem;
use App\Models\ProblemItem;
use App\Models\Product;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class InsertDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = public_path('file.json');
        $file = file_get_contents($path);
        $data = json_decode($file, true);
        foreach ($data ?? [] as $value) {
            $item = Product::query()->where('code', $value['item'])->first();
            if (!$item) {
                echo $value['item'];
                throw new Exception('Product Not Found!');
            }

            $problem = Problem::query()->firstOrCreate([
                'number' => $value['no']
            ], [
                'date' => Carbon::createFromFormat('m/d/y', $value['tgl'])->format('Y-m-d'),
                'type' => 'unit',
                'stock' => $value['po'] == 'S' ? 'stock' : 'import',
                'ri_po' => $value['nopo'],
                'status' => $value['status'] == 1 ? 'done' : 'pending',
                'email_on' => empty($value['tgl_email']) ? null : Carbon::createFromFormat('m/d/y', $value['tgl_email'])->format('Y-m-d'),
                'pic' => null,
            ]);

            $problem->items()->saveMany([new ProblemItem([
                'product_id' => $item->id,
                'qty'   => $value['qty'],
                'lot'   => $value['sn'],
                'desc'  => $value['qc'],
            ])]);
        }
    }
}
