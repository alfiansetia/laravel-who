<?php

namespace App\Console\Commands;

use App\Jobs\SyncIzinEdarJob;
use App\Services\IzinEdarSyncService;
use Illuminate\Console\Command;

class SyncIzinEdar extends Command
{
    protected $signature = 'sync:izin-edar';
    protected $description = 'Download Izin Edar Excel files from Kemkes (via queue)';

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

        $log = $this->syncService->startSync();

        $this->info('[SyncIzinEdar] Sync job dispatched to the queue. Sync ID: ' . ($log['id'] ?? '-'));
        $this->info('[SyncIzinEdar] Monitor progress via: GET /api/izin-edars/sync/progress');

        return Command::SUCCESS;
    }
}
