<?php

namespace App\Console\Commands;

use App\Services\IzinEdarSyncService;
use Illuminate\Console\Command;

class SyncIzinEdar extends Command
{
    protected $signature = 'sync:izin-edar {--no-import : Hanya download, lewati import server-side ke DB} {--import-only : Hanya import dari file tersimpan, tanpa download} {--kategori= : Batasi ke satu kategori (AKD/AKL/PKD/PKL)}';

    protected $description = 'Download Izin Edar Excel files from Kemkes + import otomatis ke DB (via queue)';

    protected IzinEdarSyncService $syncService;

    public function __construct(IzinEdarSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    public function handle(): int
    {
        // Prevent duplicate runs (the same check the API endpoint does)
        if ($this->syncService->isRunning()) {
            $this->error('[SyncIzinEdar] A sync is already in progress.');

            return Command::FAILURE;
        }

        $withImport = ! $this->option('no-import');
        $withDownload = ! $this->option('import-only');
        if (! $withImport && ! $withDownload) {
            $this->error('[SyncIzinEdar] --no-import dan --import-only tidak bisa dipakai bersamaan.');

            return Command::FAILURE;
        }
        $kategori = $this->option('kategori');
        $kategori = $kategori ? strtoupper(trim((string) $kategori)) : null;
        if ($kategori && ! isset(IzinEdarSyncService::CATEGORIES[$kategori])) {
            $this->error("[SyncIzinEdar] Kategori {$kategori} tidak dikenal. Pilih AKD/AKL/PKD/PKL.");

            return Command::FAILURE;
        }
        $log = $this->syncService->startSync($withImport, $withDownload, $kategori);

        $this->info('[SyncIzinEdar] Sync job dispatched to the queue. Sync ID: '.($log['id'] ?? '-'));
        $this->info($withDownload
            ? ($withImport
                ? '[SyncIzinEdar] Download + auto-import aktif.'
                : '[SyncIzinEdar] Mode --no-import: hanya download.')
            : '[SyncIzinEdar] Mode --import-only: import dari file tersimpan, tanpa download.');
        $this->info('[SyncIzinEdar] Monitor progress via: GET /api/izin-edars/sync/progress');

        return Command::SUCCESS;
    }
}
