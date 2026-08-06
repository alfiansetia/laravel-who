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
     * Overall job timeout. Each category download can take up to 10 minutes
     * (CURLOPT_TIMEOUT = 600s) and there are 4 files.
     *
     * NOTE: the queue worker must run with at least this timeout, e.g.
     *   php artisan queue:work database --timeout=2500 --tries=1
     *
     * retry_after must be GREATER than $timeout so the queue driver
     * doesn't reclaim this job while it is still legitimately running.
     */
    public int $timeout = 2400;

    /** Only attempt once — retries won't help for download failures. */
    public int $tries = 1;

    /** Must be greater than $timeout. */
    public int $retryAfter = 2500;

    public function __construct()
    {
        // No parameters needed — always downloads all categories.
    }

    /**
     * Execute the job: download all Excel files from Kemkes.
     * No DB import is performed here — that is handled client-side via SheetJS.
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
                    'status'      => 'failed',
                    'error'       => $e->getMessage(),
                    'finished_at' => now()->toIso8601String(),
                ]);

                $failedCategories[] = $kategori;
            }
        }

        if (empty($failedCategories)) {
            $syncService->markCompleted();
            Log::info('[SyncIzinEdar] All files downloaded successfully!');
        } else {
            $msg = 'Failed downloads: ' . implode(', ', $failedCategories);
            $syncService->markFailed($msg);
            Log::error("[SyncIzinEdar] {$msg}");
        }
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
            'status'     => 'downloading',
            'started_at' => now()->toIso8601String(),
        ]);

        $filePath = $this->downloadFile($kategori, $config['url']);

        $syncService->updateCategory($kategori, [
            'status'      => 'downloaded',
            'size'        => File::size($filePath),
            'finished_at' => now()->toIso8601String(),
        ]);

        Log::info("[SyncIzinEdar] Downloaded {$kategori} → {$filePath}");
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
}
