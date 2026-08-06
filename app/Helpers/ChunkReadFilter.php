<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * A read-filter that tells PhpSpreadsheet to only load specific row ranges.
 * Used with IOFactory reader to process large Excel files in memory-efficient chunks.
 */
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 1;
    private int $endRow   = 100;

    /**
     * Set the row range to read in the next load.
     */
    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $chunkSize;
    }

    /**
     * Called by the reader for every cell in the spreadsheet.
     * Only allow cells within our target row range to be read.
     */
    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row < $this->endRow;
    }
}
