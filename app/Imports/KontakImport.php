<?php

namespace App\Imports;

use App\Models\Kontak;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStartRow;

class KontakImport implements ToModel, WithStartRow, WithCustomCsvSettings
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        if (isset($row[0])) {
            $kontak = Kontak::firstwhere('name', $row[0]);
            if ($kontak) {
                $kontak->update([
                    'name'      => $row[0] == '' || $row[0] == '-' || $row[0] == 'FALSE' ? null : $row[0],
                    'street'    => $row[1] == '' || $row[1] == '-' || $row[1] == 'FALSE' ? null : $row[1],
                    'phone'     => $row[2] == '' || $row[2] == '-' || $row[2] == 'FALSE' ? null : $row[2],
                ]);
            } else {
                return new Kontak([
                    'name'      => $row[0] == '' || $row[0] == '-' || $row[0] == 'FALSE' ? null : $row[0],
                    'street'    => $row[1] == '' || $row[1] == '-' || $row[1] == 'FALSE' ? null : $row[1],
                    'phone'     => $row[2] == '' || $row[2] == '-' || $row[2] == 'FALSE' ? null : $row[2],
                ]);
            }
        }
    }

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
}
