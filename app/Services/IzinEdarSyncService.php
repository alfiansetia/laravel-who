<?php

namespace App\Services;

use App\Jobs\SyncIzinEdarJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class IzinEdarSyncService
{
    private const LOG_PATH = 'izin_edar_sync.json';

    public const CATEGORIES = [
        'PKD' => [
            'url' => 'https://infoalkes.kemkes.go.id/pkrt/export/MDE=',
            'file' => 'PKD.xlsx',
            'columns' => [
                'nomor_izin_edar',
                'tgl_terbit',
                'tgl_exp',
                'merk',
                'jenis_produk',
                'pendaftar',
                'alamat_pendaftar',
                'pabrik',
                'alamat_pabrik',
            ],
        ],
        'PKL' => [
            'url' => 'https://infoalkes.kemkes.go.id/pkrt/export/MDI=',
            'file' => 'PKL.xlsx',
            'columns' => [
                'nomor_izin_edar',
                'tgl_terbit',
                'tgl_exp',
                'merk',
                'jenis_produk',
                'pendaftar',
                'alamat_pendaftar',
                'pabrik',
                'alamat_pabrik',
            ],
        ],
        'AKD' => [
            'url' => 'https://infoalkes.kemkes.go.id/alkes/export/MDE=',
            'file' => 'AKD.xlsx',
            'columns' => [
                'nomor_izin_edar',
                'tgl_terbit',
                'tgl_exp',
                'merk',
                'sub_kategori',
                'jenis_produk',
                'kelompok_produk',
                'tipe',
                'kelas',
                'kelas_resiko',
                'pendaftar',
                'alamat_pendaftar',
                'pabrik',
                'alamat_pabrik',
                'pabrik2',
            ],
        ],
        'AKL' => [
            'url' => 'https://infoalkes.kemkes.go.id/alkes/export/MDI=',
            'file' => 'AKL.xlsx',
            'columns' => [
                'nomor_izin_edar',
                'tgl_terbit',
                'tgl_exp',
                'merk',
                'sub_kategori',
                'jenis_produk',
                'kelompok_produk',
                'tipe',
                'kelas',
                'kelas_resiko',
                'pendaftar',
                'alamat_pendaftar',
                'pabrik',
                'alamat_pabrik',
                'pabrik2',
            ],
        ],
    ];

    // ── File check helpers ───────────────────────────────────────────

    /**
     * Check which Excel files currently exist in the storage directory.
     * Returns an array with file info (name, path, size, modified_at).
     */
    public function checkExistingFiles(): array
    {
        $dir = storage_path('app/izin_edar');
        $results = [];

        foreach (self::CATEGORIES as $kategori => $config) {
            $filePath = $dir.DIRECTORY_SEPARATOR.$config['file'];
            $exists = File::exists($filePath);

            $results[$kategori] = [
                'file' => $config['file'],
                'exists' => $exists,
                'size' => $exists ? File::size($filePath) : 0,
                'size_human' => $exists ? $this->formatBytes(File::size($filePath)) : '-',
                'path' => $exists ? $filePath : null,
            ];
        }

        return $results;
    }

    /**
     * Get the absolute file path for a category's Excel file.
     * Returns null if the category is invalid.
     */
    public function getFilePath(string $kategori): ?string
    {
        $kategori = strtoupper($kategori);
        if (! isset(self::CATEGORIES[$kategori])) {
            return null;
        }

        return storage_path('app/izin_edar/'.self::CATEGORIES[$kategori]['file']);
    }

    /**
     * Delete a category's Excel file from storage.
     * Returns true if deleted, false if file didn't exist.
     */
    public function deleteFile(string $kategori): bool
    {
        $path = $this->getFilePath($kategori);
        if (! $path || ! File::exists($path)) {
            return false;
        }

        File::delete($path);

        return true;
    }

    /**
     * Format bytes to human-readable string.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    // ── Log helpers ──────────────────────────────────────────────────

    /** Status yang dianggap "sedang jalan" (aktif). */
    public const ACTIVE_STATUSES = ['pending', 'downloading', 'importing'];

    /** Max minutes before a sync is considered stuck/stale. */
    private const STALE_TIMEOUT_MINUTES = 120;

    public function getLogPath(): string
    {
        return storage_path('app/'.self::LOG_PATH);
    }

    public function readLog(): ?array
    {
        $path = $this->getLogPath();
        if (! File::exists($path)) {
            return null;
        }
        $raw = File::get($path);
        $log = json_decode($raw, true);
        if (! is_array($log)) {
            return null;
        }

        // Attach staleness flag
        $log['is_stale'] = $this->isStale($log);

        return $log;
    }

    public function writeLog(array $data): void
    {
        File::put($this->getLogPath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Check if a sync is currently running AND not stale.
     * Stale = running for more than STALE_TIMEOUT_MINUTES.
     */
    public function isRunning(): bool
    {
        $log = $this->readLog();
        if (! $log) {
            return false;
        }

        $isActive = in_array($log['status'] ?? '', self::ACTIVE_STATUSES);
        if (! $isActive) {
            return false;
        }

        // If it's stale, treat it as NOT running (allow new sync)
        if ($this->isStale($log)) {
            // Auto-mark the stale log as failed
            $this->markFailed('Sync timed out — no progress for '.self::STALE_TIMEOUT_MINUTES.' minutes.');

            return false;
        }

        return true;
    }

    /**
     * Check if a log entry is stale (running too long without finishing).
     */
    public function isStale(array $log): bool
    {
        $activeStatuses = self::ACTIVE_STATUSES;
        if (! in_array($log['status'] ?? '', $activeStatuses)) {
            return false;
        }

        // Check against started_at
        $startedAt = $log['started_at'] ?? null;
        if (! $startedAt) {
            return true;
        } // No start time = definitely stale

        try {
            $start = new Carbon($startedAt);

            return $start->diffInMinutes(now()) >= self::STALE_TIMEOUT_MINUTES;
        } catch (\Throwable $e) {
            return true; // Parse error = stale
        }
    }

    /**
     * Stop a running sync: set the stop flag, delete the queue job, mark stopped.
     * Returns the updated log.
     */
    public function stopSync(): array
    {
        $log = $this->readLog();

        if ($log && in_array($log['status'] ?? '', self::ACTIVE_STATUSES)) {
            // Try to delete the queue job so the worker releases it immediately
            $this->deleteQueueJob($log['job_id'] ?? null);

            $log['status'] = 'stopped';
            $log['finished_at'] = now()->toIso8601String();
            $log['error'] = 'Stopped by user.';
            $log['stop_requested'] = true;
            $log['is_stale'] = false;
            $this->writeLog($log);
        } else {
            // No active sync, just clear the file
            $this->clearLog();
        }

        return $this->readLog() ?? ['status' => 'idle'];
    }

    /**
     * Force-reset the log, clearing any stuck sync.
     * Also attempts to delete the queue job if one is tracked.
     * Returns the cleared log.
     */
    public function forceReset(): array
    {
        $log = $this->readLog();

        if ($log && in_array($log['status'] ?? '', self::ACTIVE_STATUSES)) {
            $this->deleteQueueJob($log['job_id'] ?? null);

            $log['status'] = 'failed';
            $log['finished_at'] = now()->toIso8601String();
            $log['error'] = 'Force reset by user.';
            $log['stop_requested'] = true;
            $log['is_stale'] = false;
            $this->writeLog($log);
        } else {
            // No active sync, just clear the file
            $this->clearLog();
        }

        return $this->readLog() ?? ['status' => 'idle'];
    }

    /**
     * Completely remove the log file.
     */
    public function clearLog(): void
    {
        $path = $this->getLogPath();
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    // ── Start a new sync session ─────────────────────────────────────

    public function startSync(bool $withImport = true): array
    {
        return $this->startSession($withImport);
    }

    /**
     * Start a download session for all categories.
     */
    private function startSession(bool $withImport = true): array
    {
        $categories = array_keys(self::CATEGORIES);
        $log = [
            'id' => uniqid('sync_', true),
            'status' => 'pending',
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'categories' => [],
        ];

        foreach ($categories as $cat) {
            $log['categories'][$cat] = [
                'status' => 'pending',  // pending | downloading | downloaded | importing | imported | failed
                'file' => self::CATEGORIES[$cat]['file'],
                'error' => null,
                'started_at' => null,
                'finished_at' => null,
            ];
        }

        $this->writeLog($log);

        // Dispatch the sync job to the queue and store the job ID so we can
        // target it for deletion when the user requests a stop.
        $jobId = $this->launchCommand($withImport);
        if ($jobId) {
            $log['job_id'] = $jobId;
            $this->writeLog($log);
        }

        return $log;
    }

    // ── Update category status ───────────────────────────────────────

    public function updateCategory(string $kategori, array $updates): void
    {
        $log = $this->readLog();
        if (! $log) {
            return;
        }

        $log['categories'][$kategori] = array_merge($log['categories'][$kategori] ?? [], $updates);
        $this->writeLog($log);
    }

    public function updateGlobalStatus(string $status): void
    {
        $log = $this->readLog();
        if (! $log) {
            return;
        }

        $log['status'] = $status;
        $this->writeLog($log);
    }

    public function markCompleted(): void
    {
        $log = $this->readLog();
        if (! $log) {
            return;
        }

        $log['status'] = 'completed';
        $log['finished_at'] = now()->toIso8601String();
        $this->writeLog($log);
    }

    public function markFailed(string $error): void
    {
        $log = $this->readLog();
        if (! $log) {
            return;
        }

        $log['status'] = 'failed';
        $log['finished_at'] = now()->toIso8601String();
        $log['error'] = $error;
        $this->writeLog($log);
    }

    // ── Launch sync job ──────────────────────────────────────────────

    /**
     * Dispatch the sync job onto the queue so it can run on a background
     * queue worker (managed by cron/supervisor on the server) while still
     * being monitorable via the JSON log.
     */
    /**
     * Delete the queue job from the jobs table so the worker stops processing it.
     */
    private function deleteQueueJob(?string $jobId): void
    {
        if (! $jobId) {
            return;
        }

        try {
            DB::table('jobs')
                ->where('id', $jobId)
                ->delete();
        } catch (\Throwable $e) {
            Log::warning(
                "[SyncIzinEdar] Could not delete queue job {$jobId}: {$e->getMessage()}"
            );
        }
    }

    /**
     * Dispatch the download (+ optional import) job onto the queue.
     * Response HTTP selalu cepat: dengan driver sync pun job dijalankan
     * setelah response terkirim (afterResponse), bukan inline.
     *
     * @return string|null The dispatched job ID (for tracking/deletion).
     */
    private function launchCommand(bool $withImport = true): ?string
    {
        // Driver sync mengeksekusi job inline saat push — HTTP akan ketahan
        // sampai 4 file selesai download+import. afterResponse mengirim
        // response dulu, job jalan setelahnya. Driver sync memang tidak
        // menghasilkan job ID (tidak ada baris di tabel jobs).
        if (config('queue.default') === 'sync') {
            SyncIzinEdarJob::dispatch($withImport)->afterResponse();

            return null;
        }

        $jobId = Queue::push(new SyncIzinEdarJob($withImport));

        return $jobId ? (string) $jobId : null;
    }

    // ── Server-side import (dipakai scheduler jam 01:00) ─────────────

    /**
     * Header Excel Kemkes (uppercase, trim) → kolom DB.
     * Disamakan dengan HEADER_TRANSLATION di resources/js/Pages/IzinEdar/Index.vue.
     */
    private const HEADER_MAP = [
        'NOMOR' => 'nomor_izin_edar',
        'TGL TERBIT' => 'tgl_terbit',
        'TGL EXP' => 'tgl_exp',
        'MERK' => 'merk',
        'JENIS PRODUK' => 'jenis_produk',
        'PENDAFTAR' => 'pendaftar',
        'ALAMAT PENDAFTAR' => 'alamat_pendaftar',
        'PABRIK' => 'pabrik',
        'ALAMAT PABRIK' => 'alamat_pabrik',
        'SUB KATEGORI' => 'sub_kategori',
        'KELOMPOK PRODUK' => 'kelompok_produk',
        'TIPE' => 'tipe',
        'KELAS' => 'kelas',
        'KELAS RESIKO' => 'kelas_resiko',
        'PABRIK2' => 'pabrik2',
    ];

    private const IMPORT_COLUMNS = [
        'kategori',
        'nomor_izin_edar',
        'tgl_terbit',
        'tgl_exp',
        'merk',
        'jenis_produk',
        'pendaftar',
        'alamat_pendaftar',
        'pabrik',
        'alamat_pabrik',
        'sub_kategori',
        'kelompok_produk',
        'tipe',
        'kelas',
        'kelas_resiko',
        'pabrik2',
    ];

    private const IMPORT_CAP_255 = [
        'tipe', 'merk', 'jenis_produk', 'pendaftar',
        'pabrik', 'sub_kategori', 'kelompok_produk', 'kelas_resiko',
    ];

    /**
     * Import satu file xlsx kategori ke tabel izin_edars via upsert.
     * Dipanggil SyncIzinEdarJob setelah download selesai.
     *
     * @return array{imported: int, total: int}
     */
    public function importFile(string $kategori, ?callable $shouldStop = null): array
    {
        $kategori = strtoupper($kategori);
        $path = $this->getFilePath($kategori);

        if (! $path || ! File::exists($path)) {
            throw new \RuntimeException("File {$kategori} belum di-download.");
        }

        $this->updateCategory($kategori, [
            'status' => 'importing',
            'started_at' => now()->toIso8601String(),
        ]);

        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        try {
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = (int) $sheet->getHighestDataRow();
            $highestCol = Coordinate::columnIndexFromString(
                (string) $sheet->getHighestDataColumn()
            );

            // 1. Cari baris header (maks 100 baris pertama, samakan dengan frontend).
            $headerRow = null;
            $colMap = [];
            $scanMax = min($highestRow, 100);
            for ($r = 1; $r <= $scanMax; $r++) {
                $norm = [];
                for ($c = 1; $c <= $highestCol; $c++) {
                    $v = $sheet->getCell([$c, $r])->getValue();
                    $norm[$c] = strtoupper(trim((string) ($v ?? '')));
                }
                if (in_array('NOMOR', $norm, true) || in_array('MERK', $norm, true)) {
                    $headerRow = $r;
                    foreach ($norm as $c => $h) {
                        if (isset(self::HEADER_MAP[$h])) {
                            $colMap[$c] = self::HEADER_MAP[$h];
                        }
                    }
                    break;
                }
            }

            if ($headerRow === null || empty($colMap)) {
                throw new \RuntimeException("Header tidak ditemukan di file {$kategori}.");
            }

            $total = 0;
            $imported = 0;
            $batch = [];

            $flush = function () use (&$batch, &$imported, $kategori) {
                if (empty($batch)) {
                    return;
                }
                DB::table('izin_edars')->upsert(
                    $batch,
                    ['nomor_izin_edar'],
                    array_diff(self::IMPORT_COLUMNS, ['nomor_izin_edar'])
                );
                $imported += count($batch);
                $batch = [];
                $this->updateCategory($kategori, [
                    'imported' => $imported,
                    'status' => 'importing',
                ]);
            };

            $dateCols = ['tgl_terbit', 'tgl_exp'];

            for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                if ($shouldStop && $shouldStop()) {
                    $flush();
                    throw new \RuntimeException('Stop requested.');
                }

                $record = ['kategori' => $kategori];
                $hasAny = false;
                foreach ($colMap as $c => $col) {
                    $cell = $sheet->getCell([$c, $r]);
                    $v = $cell->getValue();

                    if (Date::isDateTime($cell)) {
                        try {
                            $v = Date::excelToDateTimeObject($v)->format('Y-m-d');
                        } catch (\Throwable $e) {
                            $v = null;
                        }
                    } elseif (in_array($col, $dateCols, true)) {
                        $v = $this->normalizeDate($v);
                    } elseif (is_string($v)) {
                        $v = trim($v);
                        $v = $v === '' ? null : $v;
                    }

                    if ($v !== null && $v !== '') {
                        $hasAny = true;
                    }
                    if (is_string($v) && in_array($col, self::IMPORT_CAP_255, true)) {
                        $v = mb_substr($v, 0, 255);
                    }

                    $record[$col] = $v;
                }

                if (! $hasAny) {
                    continue;
                }
                if (empty($record['nomor_izin_edar'])) {
                    continue;
                }
                $record['nomor_izin_edar'] = trim((string) $record['nomor_izin_edar']);

                // Lengkapi kolom yang tidak ada di file dengan null.
                foreach (self::IMPORT_COLUMNS as $col) {
                    if (! array_key_exists($col, $record)) {
                        $record[$col] = null;
                    }
                }

                $total++;
                $batch[] = $record;

                if (count($batch) >= 1000) {
                    $flush();
                }
            }

            $flush();

            $this->updateCategory($kategori, [
                'status' => 'imported',
                'imported' => $imported,
                'total' => $total,
                'finished_at' => now()->toIso8601String(),
                'error' => null,
            ]);

            Log::info("[SyncIzinEdar] Imported {$kategori}: {$imported}/{$total} rows.");

            return ['imported' => $imported, 'total' => $total];
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_numeric($value)) {
            $num = (float) $value;
            // Serial Excel yang wajar: 1990-01-01 (~32874) s/d 2100 (~73050).
            if ($num > 20000 && $num < 80000) {
                try {
                    return Date::excelToDateTimeObject($num)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }

            return null;
        }
        $s = trim((string) $value);
        if ($s === '' || $s === '-' || $s === '0') {
            return null;
        }
        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
