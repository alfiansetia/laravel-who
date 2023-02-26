<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductImport implements ToModel, WithStartRow, WithCustomCsvSettings
{
    public function startRow(): int
    {
        return 4;
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
        return new Product([
            'code'      => $row[0],
            'name'      => $row[1] == '' || $row[1] == '-' || $row[1] == 'FALSE' ? null : $row[1],
            'group'     => $row[2] == '' || $row[2] == '-' || $row[2] == 'FALSE' ? null : $row[2],
            'akl'       => $row[3] == '' || $row[3] == '-' || $row[3] == 'FALSE' ? null : $row[3],
            'akl_exp'   => $row[4] == '' || $row[4] == '-' || $row[4] == 'FALSE' ? null : date('Y-m-d', strtotime(str_replace('/', '-', $row[4]))),
            'akl_file'  => $row[5] == '' || $row[5] == '-' || $row[5] == 'FALSE' ? null : $row[5],
            'category'  => $row[6] == '' || $row[6] == '-' || $row[6] == 'FALSE' ? null : $row[6],
            'vendor'    => $row[7] == '' || $row[7] == '-' || $row[7] == 'FALSE' ? null : $row[7],
            'desc'      => $row[8] == '' || $row[8] == '-' || $row[8] == 'FALSE' ? null : $row[8],
        ]);
    }
}
