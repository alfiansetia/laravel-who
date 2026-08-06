<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class IzinEdarSyncService
{
    private const LOG_PATH = 'izin_edar_sync.json';

    public const CATEGORIES = [
        'PKD' => [
            'url'  => 'https://infoalkes.kemkes.go.id/pkrt/export/MDE=',
            'file' => 'PKD.xlsx',
            'columns' => [
                'nomor_izin_edar', 'tgl_terbit', 'tgl_exp', 'merk',
                'jenis_produk', 'pendaftar', 'alamat_pendaftar', 'pabrik', 'alamat_pabrik',
            ],
        ],
        'PKL' => [
            'url'  => 'https://infoalkes.kemkes.go.id/pkrt/export/MDI=',
            'file' => 'PKL.xlsx',
            'columns' => [
                'nomor_izin_edar', 'tgl_terbit', 'tgl_exp', 'merk',
                'jenis_produk', 'pendaftar', 'alamat_pendaftar', 'pabrik', 'alamat_pabrik',
            ],
        ],
        'AKD' => [
            'url'  => 'https://infoalkes.kemkes.go.id/alkes/export/MDE=',
            'file' => 'AKD.xlsx',
            'columns' => [
                'nomor_izin_edar', 'tgl_terbit', 'tgl_exp', 'merk',
                'sub_kategori', 'jenis_produk', 'kelompok_produk', 'tipe',
                'kelas', 'kelas_resiko', 'pendaftar', 'alamat_pendaftar',
                'pabrik', 'alamat_pabrik', 'pabrik2',
            ],
        ],
        'AKL' => [
            'url'  => 'https://infoalkes.kemkes.go.id/alkes/export/MDI=',
            'file' => 'AKL.xlsx',
            'columns' => [
                'nomor_izin_edar', 'tgl_terbit', 'tgl_exp', 'merk',
                'sub_kategori', 'jenis_produk', 'kelompok_produk', 'tipe',
                'kelas', 'kelas_resiko', 'pendaftar', 'alamat_pendaftar',
                'pabrik', 'alamat_pabrik', 'pabrik2',
            ],
        ],
    ];

    // ── Log helpers ──────────────────────────────────────────────────

    /** Max minutes before a sync is considered stuck/stale. */
    private const STALE_TIMEOUT_MINUTES = 30;

    public function getLogPath(): string
    {
        return storage_path('app/' . self::LOG_PATH);
    }

    public function readLog(): ?array
    {
        $path = $this->getLogPath();
        if (!File::exists($path)) {
            return null;
        }
        $raw = File::get($path);
        $log = json_decode($raw, true);
        if (!is_array($log)) return null;

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
        if (!$log) return false;

        $isActive = in_array($log['status'] ?? '', ['pending', 'downloading', 'importing']);
        if (!$isActive) return false;

        // If it's stale, treat it as NOT running (allow new sync)
        if ($this->isStale($log)) {
            // Auto-mark the stale log as failed
            $this->markFailed('Sync timed out — no progress for ' . self::STALE_TIMEOUT_MINUTES . ' minutes.');
            return false;
        }

        return true;
    }

    /**
     * Check if a log entry is stale (running too long without finishing).
     */
    public function isStale(array $log): bool
    {
        $activeStatuses = ['pending', 'downloading', 'importing'];
        if (!in_array($log['status'] ?? '', $activeStatuses)) {
            return false;
        }

        // Check against started_at
        $startedAt = $log['started_at'] ?? null;
        if (!$startedAt) return true; // No start time = definitely stale

        try {
            $start = new \Carbon\Carbon($startedAt);
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

        if ($log && in_array($log['status'] ?? '', ['pending', 'downloading', 'importing'])) {
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

        if ($log && in_array($log['status'] ?? '', ['pending', 'downloading', 'importing'])) {
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

    public function startSync(): array
    {
        $categories = array_keys(self::CATEGORIES);
        $log = [
            'id'          => uniqid('sync_', true),
            'status'      => 'pending',       // pending | downloading | importing | completed | failed | stopped
            'started_at'  => now()->toIso8601String(),
            'finished_at' => null,
            'categories'  => [],
        ];

        foreach ($categories as $cat) {
            $log['categories'][$cat] = [
                'status'    => 'pending',  // pending | downloading | downloaded | importing | imported | failed
                'total'     => 0,
                'imported'  => 0,
                'file'      => self::CATEGORIES[$cat]['file'],
                'error'     => null,
                'started_at' => null,
                'finished_at' => null,
            ];
        }

        $this->writeLog($log);

        // Dispatch the sync job to the queue and store the job ID so we can
        // target it for deletion when the user requests a stop.
        $jobId = $this->launchCommand();
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
        if (!$log) return;

        $log['categories'][$kategori] = array_merge($log['categories'][$kategori] ?? [], $updates);
        $this->writeLog($log);
    }

    public function updateGlobalStatus(string $status): void
    {
        $log = $this->readLog();
        if (!$log) return;

        $log['status'] = $status;
        $this->writeLog($log);
    }

    public function markCompleted(): void
    {
        $log = $this->readLog();
        if (!$log) return;

        $log['status'] = 'completed';
        $log['finished_at'] = now()->toIso8601String();
        $this->writeLog($log);
    }

    public function markFailed(string $error): void
    {
        $log = $this->readLog();
        if (!$log) return;

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
        if (!$jobId) return;

        try {
            \Illuminate\Support\Facades\DB::table('jobs')
                ->where('id', $jobId)
                ->delete();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                "[SyncIzinEdar] Could not delete queue job {$jobId}: {$e->getMessage()}"
            );
        }
    }

    /**
     * Dispatch the sync job onto the queue so it can run on a background
     * queue worker (managed by cron/supervisor on the server) while still
     * being monitorable via the JSON log.
     *
     * @return string|null The dispatched job ID (for tracking/deletion).
     */
    private function launchCommand(): ?string
    {
        // Queue::push() returns the job ID directly (e.g. DB row ID for the
        // 'database' driver), which we store in the log for stop/cancel support.
        $jobId = \Illuminate\Support\Facades\Queue::push(new \App\Jobs\SyncIzinEdarJob());
        return $jobId ? (string) $jobId : null;
    }
}
