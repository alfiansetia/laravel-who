<?php

namespace App\Jobs;

use App\Models\IzinEdar;
use App\Services\IzinEdarSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SyncIzinEdarJob implements ShouldQueue
{
    use Queueable;

    /**
     * Overall job timeout. Kept high because each category download can
     * take up to 10 minutes (CURLOPT_TIMEOUT = 600s) and there are 4 files.
     *
     * NOTE: the queue worker must run with at least this timeout, e.g.
     *   php artisan queue:work database --timeout=3600 --tries=1
     */
    public int $timeout = 3600;

    public int $tries = 1;

    /**
     * Execute the job.
     */
    public function handle(IzinEdarSyncService $syncService): void
    {
        Log::info('[SyncIzinEdar] Starting sync process...');
        $syncService->updateGlobalStatus('downloading');

        $categories = IzinEdarSyncService::CATEGORIES;
        $failedCategories = [];

        foreach ($categories as $kategori => $config) {
            // Check if the user requested a stop between categories
            if ($this->isStopRequested($syncService)) {
                Log::info("[SyncIzinEdar] Stop requested — aborting before {$kategori}.");
                $syncService->updateGlobalStatus('stopped');
                $this->cleanupFiles();
                return;
            }

            try {
                $this->processCategory($kategori, $config, $syncService);
            } catch (\Throwable $e) {
                Log::error("[SyncIzinEdar] Failed category {$kategori}: {$e->getMessage()}");

                $syncService->updateCategory($kategori, [
                    'status'      => 'failed',
                    'error'       => $e->getMessage(),
                    'finished_at' => now()->toIso8601String(),
                ]);

                $failedCategories[] = $kategori;
            }
        }

        if (empty($failedCategories)) {
            $syncService->markCompleted();
            Log::info('[SyncIzinEdar] All categories synced successfully!');
        } else {
            $msg = 'Failed categories: ' . implode(', ', $failedCategories);
            $syncService->markFailed($msg);
            Log::error("[SyncIzinEdar] {$msg}");
        }

        // Cleanup downloaded files
        $this->cleanupFiles();
    }

    /**
     * Check whether the user has requested a stop via the sync log flag.
     */
    protected function isStopRequested(IzinEdarSyncService $syncService): bool
    {
        $log = $syncService->readLog();
        return ($log['stop_requested'] ?? false) === true;
    }

    protected function processCategory(string $kategori, array $config, IzinEdarSyncService $syncService): void
    {
        Log::info("[SyncIzinEdar] Processing {$kategori}...");

        // 1. Mark downloading
        $syncService->updateCategory($kategori, [
            'status'     => 'downloading',
            'started_at' => now()->toIso8601String(),
        ]);

        // 2. Download the Excel file
        $filePath = $this->downloadFile($kategori, $config['url']);

        $syncService->updateCategory($kategori, ['status' => 'downloaded']);
        Log::info("[SyncIzinEdar] Downloaded {$kategori} → {$filePath}");

        // 3. Import from Excel
        $syncService->updateGlobalStatus('importing');
        $syncService->updateCategory($kategori, ['status' => 'importing']);

        $imported = $this->importExcel($kategori, $filePath, $config['columns'], $syncService);

        $syncService->updateCategory($kategori, [
            'status'      => 'imported',
            'imported'    => $imported,
            'finished_at' => now()->toIso8601String(),
        ]);

        Log::info("[SyncIzinEdar] {$kategori}: imported {$imported} rows");
    }

    /**
     * Download the Excel file using a stream with a generous timeout.
     */
    protected function downloadFile(string $kategori, string $url): string
    {
        $destDir  = storage_path('app/izin_edar');
        File::makeDirectory($destDir, 0755, true, true);

        $filePath = $destDir . DIRECTORY_SEPARATOR . IzinEdarSyncService::CATEGORIES[$kategori]['file'];

        $ch = curl_init($url);
        $fp = fopen($filePath, 'w');

        if ($fp === false) {
            throw new \RuntimeException("Cannot open file for writing: {$filePath}");
        }

        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 600,   // 10 minutes max per file
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_NOPROGRESS     => false,
        ]);

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (!$success || $httpCode < 200 || $httpCode >= 400) {
            File::delete($filePath);
            throw new \RuntimeException(
                "Download failed for {$kategori} (HTTP {$httpCode}): {$error}"
            );
        }

        $size = File::size($filePath);
        Log::info("[SyncIzinEdar] {$kategori} file size: " . number_format($size) . ' bytes');

        return $filePath;
    }

    /**
     * Read the downloaded Excel file and import rows into izin_edars table.
     */
    protected function importExcel(string $kategori, string $filePath, array $columns, IzinEdarSyncService $syncService): int
    {
        $rows = Excel::toArray([], $filePath);

        if (empty($rows)) {
            throw new \RuntimeException("Excel file is empty for {$kategori}");
        }

        // First sheet
        $sheet = $rows[0];

        // Find header row (the first row that contains "NOMOR" or "MERK")
        $headerRowIndex = 0;
        $headerMap = [];
        $excelHeaders = [];

        foreach ($sheet as $idx => $row) {
            $normalized = array_map(fn($h) => trim(mb_strtoupper($h ?? '')), $row);
            if (in_array('NOMOR', $normalized) || in_array('MERK', $normalized)) {
                $headerRowIndex = $idx;
                $excelHeaders = $row;
                break;
            }
        }

        if (empty($excelHeaders)) {
            throw new \RuntimeException("Cannot find header row in {$kategori} Excel");
        }

        // Map Excel header names → DB column names
        $headerMap = $this->mapHeaders($excelHeaders, $kategori);

        // Data rows (skip header + any preamble rows)
        $dataRows = array_slice($sheet, $headerRowIndex + 1);
        $total = count($dataRows);
        $imported = 0;
        $batchSize = 500;
        $batch = [];

        Log::info("[SyncIzinEdar] {$kategori} total rows: {$total}");

        foreach ($dataRows as $rowIndex => $row) {
            // Skip empty rows
            if (empty(array_filter($row, fn($v) => $v !== null && $v !== '')) && $rowIndex > 0) {
                continue;
            }

            $record = $this->mapRowToRecord($row, $headerMap, $columns, $kategori);

            // Skip if nomor_izin_edar is empty
            if (empty($record['nomor_izin_edar'])) {
                continue;
            }

            $batch[] = $record;
            $imported++;

            if (count($batch) >= $batchSize) {
                $this->upsertBatch($kategori, $batch);
                $batch = [];

                // Update progress every batch
                $syncService->updateCategory($kategori, [
                    'total'    => $total,
                    'imported' => $imported,
                ]);

                if ($imported % 5000 === 0) {
                    Log::info("[SyncIzinEdar] {$kategori} progress: {$imported}/{$total}");
                }
            }
        }

        // Flush remaining batch
        if (!empty($batch)) {
            $this->upsertBatch($kategori, $batch);
        }

        $syncService->updateCategory($kategori, [
            'total'    => $total,
            'imported' => $imported,
        ]);

        return $imported;
    }

    /**
     * Map Excel header column names to our DB column names.
     */
    protected function mapHeaders(array $excelHeaders, string $kategori): array
    {
        $map = [];
        $translation = [
            'NOMOR'            => 'nomor_izin_edar',
            'TGL TERBIT'       => 'tgl_terbit',
            'TGL EXP'          => 'tgl_exp',
            'MERK'             => 'merk',
            'JENIS PRODUK'     => 'jenis_produk',
            'PENDAFTAR'        => 'pendaftar',
            'ALAMAT PENDAFTAR' => 'alamat_pendaftar',
            'PABRIK'           => 'pabrik',
            'ALAMAT PABRIK'    => 'alamat_pabrik',
            'SUB KATEGORI'     => 'sub_kategori',
            'KELOMPOK PRODUK'  => 'kelompok_produk',
            'TIPE'             => 'tipe',
            'KELAS'            => 'kelas',
            'KELAS RESIKO'     => 'kelas_resiko',
            'PABRIK2'          => 'pabrik2',
        ];

        foreach ($excelHeaders as $idx => $header) {
            $normalized = strtoupper(trim($header ?? ''));
            if (isset($translation[$normalized])) {
                $map[$idx] = $translation[$normalized];
            }
        }

        return $map;
    }

    /**
     * Convert an Excel row array to a DB record array.
     */
    protected function mapRowToRecord(array $row, array $headerMap, array $columns, string $kategori): array
    {
        $record = ['kategori' => $kategori];

        foreach ($headerMap as $colIndex => $dbColumn) {
            $value = $row[$colIndex] ?? null;

            // Trim whitespace
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') $value = null;
            }

            // Date columns: convert Y-m-d
            if (in_array($dbColumn, ['tgl_terbit', 'tgl_exp']) && $value !== null) {
                $value = $this->parseDate($value);
            }

            // Guard against "Data too long" (SQLSTATE 22001) for string columns.
            // `tipe` and other non-text columns may overflow if source data is long,
            // so cap them to the schema limit (VARCHAR = 255).
            if ($value !== null && in_array($dbColumn, [
                'tipe', 'merk', 'jenis_produk', 'pendaftar', 'pabrik',
                'sub_kategori', 'kelompok_produk', 'kelas_resiko',
            ])) {
                $value = mb_substr($value, 0, 255);
            }

            $record[$dbColumn] = $value;
        }

        // Ensure all expected columns exist
        foreach ($columns as $col) {
            if (!isset($record[$col])) {
                $record[$col] = null;
            }
        }

        return $record;
    }

    /**
     * Parse various date formats from Excel into Y-m-d.
     */
    protected function parseDate($value): ?string
    {
        if ($value === null) return null;

        // Excel serial number (numeric)
        if (is_numeric($value)) {
            $serial = (int) $value;
            if ($serial > 0 && $serial < 3000000) {
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($serial);
                    return $date->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        // String date
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return null;

            $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y', 'm/d/Y', 'Y/m/d'];
            foreach ($formats as $fmt) {
                $d = \DateTime::createFromFormat($fmt, $value);
                if ($d && $d->format($fmt) === $value) {
                    return $d->format('Y-m-d');
                }
            }

            // Try strtotime as last resort
            $ts = strtotime($value);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        return null;
    }

    /**
     * Upsert a batch of records using INSERT ... ON DUPLICATE KEY UPDATE.
     */
    protected function upsertBatch(string $kategori, array $batch): void
    {
        if (empty($batch)) return;

        $allColumns = [
            'kategori', 'nomor_izin_edar', 'tgl_terbit', 'tgl_exp', 'merk',
            'jenis_produk', 'pendaftar', 'alamat_pendaftar', 'pabrik', 'alamat_pabrik',
            'sub_kategori', 'kelompok_produk', 'tipe', 'kelas', 'kelas_resiko', 'pabrik2',
        ];

        DB::table('izin_edars')->upsert(
            $batch,
            ['nomor_izin_edar'],  // unique key
            array_diff($allColumns, ['nomor_izin_edar']) // update columns
        );
    }

    /**
     * Cleanup downloaded Excel files after import.
     */
    protected function cleanupFiles(): void
    {
        $dir = storage_path('app/izin_edar');
        if (File::isDirectory($dir)) {
            $files = glob($dir . DIRECTORY_SEPARATOR . '*.xlsx');
            foreach ($files as $f) {
                File::delete($f);
            }
            Log::info('[SyncIzinEdar] Cleaned up downloaded files.');
        }
    }
}