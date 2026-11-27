<?php

namespace App\Jobs;

use App\Services\IzinEdarSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SyncIzinEdarJob implements ShouldQueue
{
    use Queueable;

    /**
     * Overall job timeout. Download 4 file @ maks 10 menit + import
     * server-side (upsert ribuan baris per file).
     *
     * NOTE: queue worker harus jalan dengan timeout minimal sebesar ini, e.g.
     *   php artisan queue:work database --timeout=3600 --tries=1
     * Docker: sesuaikan --timeout di docker-compose.yml (who-worker).
     *
     * retry_after must be GREATER than $timeout so the queue driver
     * doesn't reclaim this job while it is still legitimately running.
     */
    public int $timeout = 3500;

    /** Only attempt once — retries won't help for download failures. */
    public int $tries = 1;

    /** Must be greater than $timeout. */
    public int $retryAfter = 3600;

    public bool $shouldImport;

    public function __construct(bool $shouldImport = true)
    {
        $this->shouldImport = $shouldImport;
    }

    /**
     * Execute the job: download all Excel files from Kemkes,
     * lalu import otomatis ke DB bila $shouldImport true (jadwal jam 01:00).
     */
    public function handle(IzinEdarSyncService $syncService): void
    {
        Log::info('[SyncIzinEdar] Starting download...');
        $syncService->updateGlobalStatus('downloading');

        $categories = IzinEdarSyncService::CATEGORIES;
        $failedCategories = [];

        foreach ($categories as $kategori => $config) {
            // Check if the user requested a stop between categories
            if ($this->isStopRequested($syncService)) {
                Log::info("[SyncIzinEdar] Stop requested — aborting before {$kategori}.");
                $syncService->updateGlobalStatus('stopped');

                return;
            }

            try {
                $this->downloadCategory($kategori, $config, $syncService);
            } catch (\Throwable $e) {
                Log::error("[SyncIzinEdar] Failed downloading {$kategori}: {$e->getMessage()}");

                $syncService->updateCategory($kategori, [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'finished_at' => now()->toIso8601String(),
                ]);

                $failedCategories[] = $kategori;

                continue;
            }

            // Import otomatis server-side (jadwal jam 01:00). Stop request
            // dicek juga di dalam importFile per baris via callback.
            if ($this->shouldImport) {
                try {
                    $syncService->updateGlobalStatus('importing');
                    $syncService->importFile($kategori, fn () => $this->isStopRequested($syncService));
                } catch (\Throwable $e) {
                    if (str_contains($e->getMessage(), 'Stop requested')) {
                        Log::info("[SyncIzinEdar] Stop requested — aborting before {$kategori} import selesai.");
                        $syncService->updateGlobalStatus('stopped');

                        return;
                    }

                    Log::error("[SyncIzinEdar] Failed importing {$kategori}: {$e->getMessage()}");

                    $syncService->updateCategory($kategori, [
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'finished_at' => now()->toIso8601String(),
                    ]);

                    $failedCategories[] = $kategori;
                }
            }
        }

        if (empty($failedCategories)) {
            $syncService->markCompleted();
            Log::info('[SyncIzinEdar] All files downloaded successfully!');
        } else {
            $msg = 'Failed downloads: '.implode(', ', $failedCategories);
            $syncService->markFailed($msg);
            Log::error("[SyncIzinEdar] {$msg}");
        }
    }

    /**
     * Called by Laravel when the job exhausts tries or the worker kills it
     * via exception. Guarantees the JSON log doesn't stay stuck in
     * downloading/importing forever (which the stale-checker would later
     * report as "timed out — process was likely interrupted").
     */
    public function failed(\Throwable $exception): void
    {
        try {
            app(IzinEdarSyncService::class)->markFailed('Job failed: '.$exception->getMessage());
        } catch (\Throwable $e) {
            Log::error('[SyncIzinEdar] failed() handler error: '.$e->getMessage());
        }
        Log::error('[SyncIzinEdar] Job failed: '.$exception->getMessage());
    }

    /**
     * Check whether the user has requested a stop via the sync log flag.
     */
    protected function isStopRequested(IzinEdarSyncService $syncService): bool
    {
        $log = $syncService->readLog();

        return ($log['stop_requested'] ?? false) === true;
    }

    /**
     * Download a single category's Excel file from Kemkes.
     */
    protected function downloadCategory(string $kategori, array $config, IzinEdarSyncService $syncService): void
    {
        Log::info("[SyncIzinEdar] Downloading {$kategori}...");

        $syncService->updateCategory($kategori, [
            'status' => 'downloading',
            'started_at' => now()->toIso8601String(),
        ]);

        $filePath = $this->downloadFile($kategori, $config['url'], $syncService);

        $syncService->updateCategory($kategori, [
            'status' => 'downloaded',
            'size' => File::size($filePath),
            'finished_at' => now()->toIso8601String(),
        ]);

        Log::info("[SyncIzinEdar] Downloaded {$kategori} → {$filePath}");
    }

    /**
     * Download the Excel file using a stream with a generous timeout.
     * Sends a heartbeat to the sync log every ~10s so the stale-checker
     * (based on last activity) doesn't kill a slow-but-alive download.
     */
    protected function downloadFile(string $kategori, string $url, ?IzinEdarSyncService $syncService = null): string
    {
        $destDir = storage_path('app/izin_edar');
        File::makeDirectory($destDir, 0755, true, true);

        $filePath = $destDir.DIRECTORY_SEPARATOR.IzinEdarSyncService::CATEGORIES[$kategori]['file'];

        $ch = curl_init($url);
        $fp = fopen($filePath, 'w');

        if ($fp === false) {
            throw new \RuntimeException("Cannot open file for writing: {$filePath}");
        }

        $lastBeat = 0;
        $progressFn = function ($ch, $dlTotal, $downloaded) use ($kategori, $syncService, &$lastBeat) {
            $now = time();
            // Throttle: max 1 log write per 10s to avoid disk churn.
            if ($syncService && $now - $lastBeat >= 10) {
                $lastBeat = $now;
                try {
                    $syncService->updateCategory($kategori, [
                        'status' => 'downloading',
                        'downloaded_bytes' => (int) $downloaded,
                        'expected_bytes' => $dlTotal > 0 ? (int) $dlTotal : null,
                    ]);
                } catch (\Throwable $e) {
                    // Never let heartbeat break the download.
                }
            }

            return 0; // return non-zero aborts transfer
        };

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 600,   // 10 minutes max per file
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => $progressFn,
        ]);

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (! $success || $httpCode < 200 || $httpCode >= 400) {
            File::delete($filePath);
            throw new \RuntimeException(
                "Download failed for {$kategori} (HTTP {$httpCode}): {$error}"
            );
        }

        $size = File::size($filePath);
        Log::info("[SyncIzinEdar] {$kategori} file size: ".number_format($size).' bytes');

        return $filePath;
    }
}
