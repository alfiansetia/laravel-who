<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Filter baca PhpSpreadsheet: hanya muat rentang baris tertentu.
 * Dipakai import file besar per chunk agar sheet tidak full di RAM.
 */
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 1;

    private int $endRow = 1;

    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = max($startRow, 1);
        $this->endRow = $this->startRow + max($chunkSize, 1);
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row < $this->endRow;
    }
}
