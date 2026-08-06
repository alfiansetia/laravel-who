<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IzinEdar;
use App\Services\IzinEdarSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IzinEdarController extends Controller
{
    protected IzinEdarSyncService $syncService;

    public function __construct(IzinEdarSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display a paginated listing of izin edar with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 50), 200);
        $page = max((int) $request->input('page', 1), 1);

        $query = IzinEdar::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_izin_edar', 'like', "%{$keyword}%")
                  ->orWhere('merk', 'like', "%{$keyword}%")
                  ->orWhere('pendaftar', 'like', "%{$keyword}%")
                  ->orWhere('jenis_produk', 'like', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();

        $data = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'id', 'kategori', 'nomor_izin_edar', 'tgl_terbit', 'tgl_exp',
                'merk', 'jenis_produk', 'pendaftar', 'pabrik',
                'alamat_pendaftar', 'alamat_pabrik',
                'sub_kategori', 'kelompok_produk', 'tipe', 'kelas', 'kelas_resiko', 'pabrik2',
            ]);

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    /**
     * Display the specified izin edar.
     */
    public function show(IzinEdar $izinEdar): JsonResponse
    {
        return response()->json(['data' => $izinEdar]);
    }

    /**
     * Check the current sync progress from the JSON log.
     * GET /api/izin-edars/sync/progress
     */
    public function syncProgress(): JsonResponse
    {
        $log = $this->syncService->readLog();

        if (!$log) {
            return response()->json([
                'status' => 'idle',
                'message' => 'No sync has been started yet.',
                'is_stale' => false,
            ]);
        }

        // Compute overall progress percentage
        $totalImported = 0;
        $totalExpected = 0;
        $totalFiles    = count($log['categories'] ?? []);

        foreach (($log['categories'] ?? []) as $cat) {
            $totalImported += $cat['imported'] ?? 0;
            $totalExpected += $cat['total'] ?? 0;
        }

        $log['progress_percent'] = $totalExpected > 0
            ? round(($totalImported / $totalExpected) * 100, 1)
            : 0;
        $log['total_imported'] = $totalImported;
        $log['total_expected'] = $totalExpected;
        $log['total_files']    = $totalFiles;

        // Convenience flags for the frontend
        $activeStatuses = ['pending', 'downloading', 'importing'];
        $log['is_running'] = in_array($log['status'] ?? '', $activeStatuses);
        $log['can_stop']   = $log['is_running'];
        $log['can_start']  = !$log['is_running'];

        // If stale, auto-mark as failed
        if (($log['is_stale'] ?? false) && in_array($log['status'] ?? '', ['pending', 'downloading', 'importing'])) {
            $this->syncService->markFailed('Sync timed out — no progress for 30 minutes. Process was likely interrupted.');
            $log['status'] = 'failed';
            $log['is_stale'] = false;
            $log['error'] = 'Sync timed out — no progress for 30 minutes.';
        }

        return response()->json($log);
    }

    /**
     * Trigger a sync process.
     * POST /api/izin-edars/sync
     */
    public function sync(): JsonResponse
    {
        // Prevent duplicate runs
        if ($this->syncService->isRunning()) {
            $log = $this->syncService->readLog();
            return response()->json([
                'success' => false,
                'message' => 'A sync is already in progress.',
                'log'     => $log,
                'can_stop' => true,
            ], 409);
        }

        $log = $this->syncService->startSync();

        return response()->json([
            'success' => true,
            'message' => 'Sync started. This process may take several minutes.',
            'log'     => $log,
        ]);
    }

    /**
     * Stop a running sync and allow the user to restart.
     * POST /api/izin-edars/sync/stop
     *
     * This sets a stop flag in the log (the job checks it between categories),
     * and attempts to delete the job from the queue so the worker stops it immediately.
     */
    public function syncStop(): JsonResponse
    {
        $log = $this->syncService->readLog();

        if (!$log || !in_array($log['status'] ?? '', ['pending', 'downloading', 'importing'])) {
            return response()->json([
                'success' => true,
                'message' => 'No sync is currently running.',
                'log'     => $log ?? ['status' => 'idle'],
            ]);
        }

        $log = $this->syncService->stopSync();

        return response()->json([
            'success' => true,
            'message' => 'Sync stopped. You can start a new sync now.',
            'log'     => $log,
        ]);
    }

    /**
     * Force-reset a stuck sync log.
     * DELETE /api/izin-edars/sync/reset
     */
    public function syncReset(): JsonResponse
    {
        $log = $this->syncService->forceReset();

        return response()->json([
            'success' => true,
            'message' => 'Sync log has been reset. You can start a new sync now.',
            'log'     => $log,
        ]);
    }
}
