<?php

namespace App\Imports;

use App\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductImport implements ToModel, WithStartRow, WithCustomCsvSettings
// , WithStartRow, WithCustomCsvSettings
{
    public function startRow(): int
    {
        return 2;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (isset($row[0])) {
            $prod = Product::firstwhere('code', $row[0]);
            if ($prod) {
                $prod->update([
                    'code'      => $row[0],
                    'name'      => $row[1] == '' || $row[1] == '-' || $row[1] == 'FALSE' ? null : $row[1],
                    // 'group'     => $row[2] == '' || $row[2] == '-' || $row[2] == 'FALSE' ? null : $row[2],
                    'akl'       => $row[2] == '' || $row[2] == '-' || $row[2] == 'FALSE' ? null : $row[2],
                    // 'akl_exp'   => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : date('Y-m-d', strtotime(str_replace('/', '-', $row[4]))),
                    // 'akl_exp'   => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : date('Y-m-d', strtotime($row[4])),
                    'akl_exp'   => $row[3] == '' || $row[3] == '-' || $row[3] == 'FALSE' ? null : Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($row[3] - 2)->format('Y-m-d'),
                    // 'akl_exp'   => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : $row[4],
                    // 'akl_file'  => $row[5] == '' || $row[5] == '-' || $row[5] == 'FALSE' ? null : $row[5],
                    // 'category'  => $row[6] == '' || $row[6] == '-' || $row[6] == 'FALSE' ? null : $row[6],
                    // 'vendor'    => $row[7] == '' || $row[7] == '-' || $row[7] == 'FALSE' ? null : $row[7],
                    'desc'      => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : $row[4],
                ]);
            } else {
                return new Product([
                    'code'      => $row[0],
                    'name'      => $row[1] == '' || $row[1] == '-' || $row[1] == 'FALSE' ? null : $row[1],
                    // 'group'     => $row[2] == '' || $row[2] == '-' || $row[2] == 'FALSE' ? null : $row[2],
                    'akl'       => $row[2] == '' || $row[2] == '-' || $row[2] == 'FALSE' ? null : $row[2],
                    // 'akl_exp'   => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : date('Y-m-d', strtotime($row[4])),
                    'akl_exp'   => $row[3] == '' || $row[3] == '-' || $row[3] == 'FALSE' ? null : Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($row[3] - 2)->format('Y-m-d'),
                    // 'akl_exp'   => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : $row[4],
                    // 'akl_exp'   => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : date('Y-m-d', strtotime(str_replace('/', '-', $row[4]))),
                    // 'akl_file'  => $row[5] == '' || $row[5] == '-' || $row[5] == 'FALSE' ? null : $row[5],
                    // 'category'  => $row[6] == '' || $row[6] == '-' || $row[6] == 'FALSE' ? null : $row[6],
                    // 'vendor'    => $row[7] == '' || $row[7] == '-' || $row[7] == 'FALSE' ? null : $row[7],
                    'desc'      => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : $row[4],
                ]);
            }
        }
    }
}
