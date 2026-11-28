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
            $mtime = $exists ? File::lastModified($filePath) : 0;

            $results[$kategori] = [
                'file' => $config['file'],
                'exists' => $exists,
                'size' => $exists ? File::size($filePath) : 0,
                'size_human' => $exists ? $this->formatBytes(File::size($filePath)) : '-',
                'path' => $exists ? $filePath : null,
                'modified_at' => $mtime > 0 ? $mtime : null,
                'modified_human' => $mtime > 0
                    ? Carbon::createFromTimestamp($mtime)->setTimezone(config('app.timezone'))->format('d/m/Y H:i')
                    : '-',
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

    /** Max minutes without any progress before a sync is considered stuck/stale. */
    public const STALE_TIMEOUT_MINUTES = 120;

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
     * Check if a log entry is stale (no progress for STALE_TIMEOUT_MINUTES).
     * Uses last activity (log file mtime, updated on every progress write)
     * instead of started_at, so a slow-but-progressing sync (e.g. AKL 5.5MB)
     * is NOT wrongly marked as timed out.
     */
    public function isStale(array $log): bool
    {
        if (! in_array($log['status'] ?? '', self::ACTIVE_STATUSES)) {
            return false;
        }

        $lastActivity = $this->resolveLastActivity($log);
        if ($lastActivity === null) {
            return true; // No timestamp at all = definitely stale
        }

        return $lastActivity->diffInMinutes(now()) >= self::STALE_TIMEOUT_MINUTES;
    }

    /**
     * Resolve the most recent activity timestamp for a sync log:
     * max(file mtime, started_at, per-category started_at/finished_at).
     */
    private function resolveLastActivity(array $log): ?Carbon
    {
        $candidates = [];

        // File mtime = bumped on every updateCategory/updateGlobalStatus.
        try {
            $mtime = File::lastModified($this->getLogPath());
            if ($mtime > 0) {
                $candidates[] = Carbon::createFromTimestamp($mtime);
            }
        } catch (\Throwable $e) {
            // fall through to timestamp fields
        }

        foreach (['started_at', 'finished_at'] as $field) {
            if (! empty($log[$field])) {
                try {
                    $candidates[] = new Carbon($log[$field]);
                } catch (\Throwable $e) {
                    // ignore unparseable
                }
            }
        }

        foreach (($log['categories'] ?? []) as $cat) {
            foreach (['started_at', 'finished_at'] as $field) {
                if (! empty($cat[$field])) {
                    try {
                        $candidates[] = new Carbon($cat[$field]);
                    } catch (\Throwable $e) {
                        // ignore unparseable
                    }
                }
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, fn (Carbon $a, Carbon $b) => $a->getTimestamp() <=> $b->getTimestamp());

        return end($candidates) ?: null;
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

    public function startSync(bool $withImport = true, bool $withDownload = true, ?string $kategori = null): array
    {
        return $this->startSession($withImport, $withDownload, $kategori);
    }

    /**
     * Start a download (+ optional import) session for all categories,
     * atau satu kategori bila $kategori diisi (untuk import-only per file).
     */
    private function startSession(bool $withImport = true, bool $withDownload = true, ?string $kategori = null): array
    {
        $kategori = $kategori ? strtoupper(trim($kategori)) : null;
        if ($kategori && ! isset(self::CATEGORIES[$kategori])) {
            throw new \InvalidArgumentException("Kategori {$kategori} tidak dikenal.");
        }
        $categories = $kategori ? [$kategori] : array_keys(self::CATEGORIES);
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
        $jobId = $this->launchCommand($withImport, $withDownload, $kategori);
        if ($jobId) {
            $log['job_id'] = $jobId;
            $this->writeLog($log);
        }

        return $log;
    }

    // ── Update category status ───────────────────────────────────────

    /**
     * Progres satu kategori (0–100) untuk rata-rata progres global.
     * Berbasis baris saat import, berbasis byte saat download, jadi bar
     * tidak pernah klaim 100% sebelum kategorinya benar-benar selesai.
     */
    public function categoryProgress(array $cat): float
    {
        $status = $cat['status'] ?? 'pending';

        if (in_array($status, ['imported', 'failed'], true)) {
            return 100.0;
        }

        if ($status === 'importing') {
            $total = (int) ($cat['total'] ?? 0);
            $imported = (int) ($cat['imported'] ?? 0);
            // Baris terpindai (termasuk baris kosong yang dilewati) agar bar
            // bergerak tiap chunk, bukan cuma tiap flush 1000 baris.
            $processed = (int) ($cat['processed'] ?? $imported);
            if ($total > 0) {
                $done = max($imported, min($processed, $total));

                return min(($done / $total) * 100, 99.9);
            }

            // Estimasi total belum tertulis (fase awal scan header).
            return 50.0;
        }

        // Download beres, antre import (sync jalan 2 fase).
        if ($status === 'downloaded') {
            return 50.0;
        }

        if ($status === 'downloading') {
            $expected = (int) ($cat['expected_bytes'] ?? 0);
            $got = (int) ($cat['downloaded_bytes'] ?? 0);
            if ($expected > 0 && $got >= 0) {
                return min(($got / $expected) * 50, 49.9);
            }

            return 25.0;
        }

        return 0.0;
    }

    public function updateCategory(string $kategori, array $updates): void
    {
        $log = $this->readLog();
        if (! $log) {
            return;
        }

        $log['categories'][$kategori] = array_merge($log['categories'][$kategori] ?? [], $updates);
        $this->writeLog($log);
    }

    /**
     * Heartbeat import dengan laju EMA (baris/detik) dari selisih progres
     * terhadap heartbeat sebelumnya — bukan rata-rata sejak awal import.
     * Rata-rata seumur hidup kacau saat timer ke-reset (retry / Lepas /
     * job ganda) atau fase awal cepat lalu fase AKL lambat: ETA mentok
     * "± 1 detik" padahal sisa masih menit. Di sini counter turun
     * (restart) me-reset laju, dan selisih waktu < 1 detik dilewati agar
     * tidak meledak.
     */
    public function pushImportHeartbeat(string $kategori, int $imported, int $processed): void
    {
        $log = $this->readLog();
        if (! $log) {
            return;
        }

        $cat = $log['categories'][$kategori] ?? [];
        $now = microtime(true);
        $rate = isset($cat['rate_per_sec']) ? (float) $cat['rate_per_sec'] : null;
        $prevAt = isset($cat['heartbeat_at']) ? (float) $cat['heartbeat_at'] : 0.0;
        $prevImported = isset($cat['imported']) ? (int) $cat['imported'] : 0;

        if ($imported < $prevImported) {
            // Counter turun = import dimulai ulang: buang laju lama.
            $rate = null;
        } elseif ($prevAt > 0 && ($now - $prevAt) >= 1.0) {
            $inst = ($imported - $prevImported) / ($now - $prevAt);
            if ($inst >= 0) {
                $rate = $rate === null ? $inst : (0.3 * $inst) + (0.7 * $rate);
            }
        }

        $updates = [
            'status' => 'importing',
            'imported' => $imported,
            'processed' => $processed,
            'heartbeat_at' => $now,
            'memory_mb' => round(memory_get_usage(true) / 1048576, 1),
        ];
        if ($rate !== null && $rate > 0) {
            $updates['rate_per_sec'] = round($rate, 1);
        }

        $log['categories'][$kategori] = array_merge($cat, $updates);
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

    /**
     * Tandai fase import dimulai + catat waktunya untuk hitung ETA.
     */
    public function markImporting(): void
    {
        $log = $this->readLog();
        if (! $log) {
            return;
        }

        $log['status'] = 'importing';
        $log['import_started_at'] = now()->toIso8601String();
        $this->writeLog($log);
    }

    /**
     * Format detik ETA ke bahasa manusia: "45 detik", "12 menit", "1 jam 5 menit".
     */
    public static function formatEta(?int $seconds): string
    {
        if ($seconds === null || $seconds < 0) {
            return '-';
        }
        if ($seconds < 60) {
            return "± {$seconds} detik lagi";
        }
        $minutes = (int) floor($seconds / 60);
        if ($minutes < 60) {
            return "± {$minutes} menit lagi";
        }
        $hours = (int) floor($minutes / 60);
        $rest = $minutes % 60;

        return $rest > 0 ? "± {$hours} jam {$rest} menit lagi" : "± {$hours} jam lagi";
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
    private function launchCommand(bool $withImport = true, bool $withDownload = true, ?string $kategori = null): ?string
    {
        // Driver sync mengeksekusi job inline saat push — HTTP akan ketahan
        // sampai 4 file selesai download+import. afterResponse mengirim
        // response dulu, job jalan setelahnya. Driver sync memang tidak
        // menghasilkan job ID (tidak ada baris di tabel jobs).
        if (config('queue.default') === 'sync') {
            SyncIzinEdarJob::dispatch($withImport, $withDownload, $kategori)->afterResponse();

            return null;
        }

        $jobId = Queue::push(new SyncIzinEdarJob($withImport, $withDownload, $kategori));

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
     * Batas memori proses import: kalau file kebesaran, yang mati hanya job
     * ini (masuk failed + log failed) — bukan seluruh VPS. Hanya menaikkan,
     * tidak menurunkan (limit admin yang lebih besar tetap dihormati),
     * dan mengikat limit unlimited (-1, default CLI) yang paling berbahaya.
     */
    private const JOB_MEMORY_LIMIT = '1G';

    /**
     * Import satu file xlsx kategori ke tabel izin_edars via upsert.
     * File dibaca per WINDOW (default 10.000 baris per load): tiap load
     * mem-parse ulang seluruh file, jadi makin sedikit load makin hemat
     * CPU+RAM. Upsert + heartbeat tetap per 1000 baris.
     * Dipanggil SyncIzinEdarJob setelah download selesai.
     *
     * @return array{imported: int, total: int}
     */
    public function importFile(string $kategori, ?callable $shouldStop = null, int $chunkSize = 1000, int $loadWindow = 10000): array
    {
        $kategori = strtoupper($kategori);
        $path = $this->getFilePath($kategori);

        if (! $path || ! File::exists($path)) {
            throw new \RuntimeException("File {$kategori} belum di-download.");
        }

        $cap = $this->memoryLimitBytes(self::JOB_MEMORY_LIMIT);
        $current = $this->memoryLimitBytes();
        // Selalu enforce (bukan cuma menaikkan): limit PHP yang lebih besar
        // dari cap — mis. 2G di image production — tetap berbahaya di VPS
        // kecil serumah MySQL. Yang butuh lebih, ubah konstantanya.
        if ($current !== $cap) {
            ini_set('memory_limit', self::JOB_MEMORY_LIMIT);
        }
        // Turunkan prioritas CPU proses ini: import tetap full speed saat
        // box idle, tapi mengalah ke web/SSH/MySQL saat berebut CPU.
        if (function_exists('proc_nice')) {
            @proc_nice(10);
        }

        $this->updateCategory($kategori, [
            'status' => 'importing',
            'started_at' => now()->toIso8601String(),
        ]);
        // Dimensi sheet tanpa memuat isi sel (murah, tidak makan RAM).
        $worksheetInfo = (new Xlsx)->listWorksheetInfo($path);
        if (empty($worksheetInfo)) {
            throw new \RuntimeException("Sheet tidak ditemukan di file {$kategori}.");
        }
        $sheetName = (string) $worksheetInfo[0]['worksheetName'];
        $highestRow = max((int) $worksheetInfo[0]['totalRows'], 1);
        $highestCol = Coordinate::columnIndexFromString(
            (string) $worksheetInfo[0]['lastColumnLetter']
        );

        // Tulis estimasi total SEBELUM scan header: kalau worker mati saat
        // load pertama (file besar), log tidak beku tanpa total/imported —
        // UI menampilkan 0% + "memindai…", bukan rasio 100% palsu.
        // Angka aktual ditimpa saat import selesai.
        $this->updateCategory($kategori, [
            'total' => max($highestRow - 1, 0),
            'total_estimated' => true,
            'imported' => 0,
            'processed' => 0,
        ]);

        $loadChunk = function (int $startRow, int $rows) use ($path, $sheetName) {
            $filter = new ChunkReadFilter;
            $filter->setRows($startRow, $rows);
            $reader = new Xlsx;
            $reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly([$sheetName]);
            $reader->setReadFilter($filter);

            return $reader->load($path);
        };

        // 1. Cari baris header: muat 100 baris pertama saja (samakan dengan frontend).
        $headerRow = null;
        $colMap = [];
        $spreadsheet = $loadChunk(1, 100);
        try {
            $sheet = $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet();
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
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }

        if ($headerRow === null || empty($colMap)) {
            throw new \RuntimeException("Header tidak ditemukan di file {$kategori}.");
        }

        // Persempit estimasi total setelah baris header ketemu
        // (tetap estimasi sampai import selesai: ekor kosong difinalkan nanti).
        $this->updateCategory($kategori, [
            'total' => max($highestRow - $headerRow, 0),
            'total_estimated' => true,
        ]);

        $total = 0;
        $imported = 0;
        $batch = [];

        $flush = function () use (&$batch, &$imported, &$processed, $kategori) {
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
            $this->pushImportHeartbeat($kategori, $imported, $processed);
        };

        $dateCols = ['tgl_terbit', 'tgl_exp'];
        $emptyStreak = 0;
        $processed = 0;

        // 2. Loop data per WINDOW: satu load untuk N baris (bukan satu load
        // per 1000 baris). Tiap load mem-parse ulang seluruh file, jadi 64
        // load untuk AKL dipadatkan menjadi ~7 load — jauh lebih hemat CPU
        // dan menumpuk lebih sedikit kebocoran memori. Upsert + heartbeat
        // tetap per 1000 baris. Cek stop per window + per 1000 baris.
        $loadWindow = max($loadWindow, $chunkSize);
        for ($winStart = $headerRow + 1; $winStart <= $highestRow; $winStart += $loadWindow) {
            if ($shouldStop && $shouldStop()) {
                $flush();
                throw new \RuntimeException('Stop requested.');
            }

            // Gagal cepat dengan pesan jelas bila memori hampir habis,
            // supaya tercatat `failed` — bukan beku `importing` + job reserved,
            // dan yang mati hanya proses ini, bukan VPS.
            $this->assertMemoryForWindow($kategori, $winStart);

            $winEnd = min($winStart + $loadWindow - 1, $highestRow);
            $spreadsheet = $loadChunk($winStart, $winEnd - $winStart + 1);
            try {
                $sheet = $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet();

                for ($r = $winStart; $r <= $winEnd; $r++) {
                    $processed++;
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
                        // Ekor file sering berisi baris kosong terformat:
                        // berhenti setelah 500 baris kosong beruntun.
                        if (++$emptyStreak >= 500) {
                            break 2;
                        }

                        continue;
                    }
                    $emptyStreak = 0;
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

                    // Heartbeat tiap 1000 baris terpindai (bukan cuma tiap
                    // flush): baris kosong tidak memicu flush, tanpa ini log
                    // beku dan stale-checker buta selama worker masih memindai.
                    if ($processed % 1000 === 0) {
                        if ($shouldStop && $shouldStop()) {
                            $flush();
                            throw new \RuntimeException('Stop requested.');
                        }
                        $this->pushImportHeartbeat($kategori, $imported, $processed);
                    }
                }
            } finally {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet, $sheet);
                gc_collect_cycles();
            }

            // Heartbeat per window: menangkap sisa < 1000 baris terakhir.
            $this->pushImportHeartbeat($kategori, $imported, $processed);
        }

        $flush();

        $this->updateCategory($kategori, [
            'status' => 'imported',
            'imported' => $imported,
            'total' => $total,
            'total_estimated' => false,
            'finished_at' => now()->toIso8601String(),
            'error' => null,
        ]);

        Log::info("[SyncIzinEdar] Imported {$kategori}: {$imported}/{$total} rows.");

        return ['imported' => $imported, 'total' => $total];
    }

    /**
     * Gagal cepat bila memori hampir habis sebelum load chunk berikutnya.
     * PhpSpreadsheet mem-parse shared-strings seluruh file tiap load, jadi
     * file besar (mis. AKL) bisa menghabiskan RAM di tengah jalan. Tanpa
     * ini PHP mati fatal: tidak ada exception, log beku `importing`, dan
     * job tertinggal `reserved` selamanya.
     */
    private function assertMemoryForWindow(string $kategori, int $startRow): void
    {
        $limit = $this->memoryLimitBytes();
        if ($limit <= 0) {
            return; // unlimited (-1): tidak bisa diprediksi, lewati.
        }
        $used = memory_get_usage(true);
        if ($used / $limit < 0.85) {
            return;
        }
        $usedMb = round($used / 1048576, 1);
        $limitMb = round($limit / 1048576, 1);
        throw new \RuntimeException(
            "Memori hampir habis saat import {$kategori} baris {$startRow} ({$usedMb}/{$limitMb} MB). ".
            'Naikkan memory_limit worker queue lalu ulangi import-only kategori ini.'
        );
    }

    private function memoryLimitBytes(?string $raw = null): int
    {
        $raw = $raw ?? trim((string) ini_get('memory_limit'));
        if ($raw === '' || $raw === '-1') {
            return -1;
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*([KMG]?)/i', $raw, $m)) {
            $num = (float) $m[1];
            $mult = match (strtoupper($m[2] ?? '')) {
                'G' => 1073741824,
                'M' => 1048576,
                'K' => 1024,
                default => 1,
            };

            return (int) ($num * $mult);
        }

        return -1;
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
