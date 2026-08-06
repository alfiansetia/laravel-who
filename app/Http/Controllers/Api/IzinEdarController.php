<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IzinEdar;
use App\Services\IzinEdarSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SyncIzinEdarJob;
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
                'id',
                'kategori',
                'nomor_izin_edar',
                'tgl_terbit',
                'tgl_exp',
                'merk',
                'jenis_produk',
                'pendaftar',
                'pabrik',
                'alamat_pendaftar',
                'alamat_pabrik',
                'sub_kategori',
                'kelompok_produk',
                'tipe',
                'kelas',
                'kelas_resiko',
                'pabrik2',
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

    /**
     * Check which Excel files from a previous download still exist on disk.
     * GET /api/izin-edars/sync/files
     */
    public function checkFiles(): JsonResponse
    {
        $files = $this->syncService->checkExistingFiles();

        $hasAny = collect($files)->contains('exists', true);

        return response()->json([
            'success'  => true,
            'files'    => $files,
            'has_any'  => $hasAny,
            'message'  => $hasAny
                ? 'File Excel ditemukan dan siap di-import.'
                : 'Tidak ada file Excel yang tersimpan. Silakan sync data terlebih dahulu.',
        ]);
    }

    /**
     * Receive a batch of pre-extracted rows from the client and upsert them.
     * POST /api/izin-edars/import-batch
     *
     * This is the client-side parsing approach: the browser reads the Excel
     * file with SheetJS and sends extracted data in batches.
     */
    public function importBatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kategori'     => 'required|string|in:AKD,AKL,PKD,PKL',
            'rows'         => 'required|array|min:1|max:1000',
            'rows.*.nomor_izin_edar' => 'required|string',
        ], [
            'kategori.required' => 'Kategori wajib diisi.',
            'kategori.in'       => 'Kategori tidak valid.',
            'rows.required'     => 'Data baris wajib diisi.',
            'rows.max'          => 'Maksimal 1000 baris per batch.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $kategori = $request->input('kategori');
        $rows     = $request->input('rows');

        $allColumns = [
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

        $batch = [];
        foreach ($rows as $row) {
            $record = ['kategori' => $kategori];

            foreach ($allColumns as $col) {
                if ($col === 'kategori') continue;

                $value = $row[$col] ?? null;

                // Trim strings
                if (is_string($value)) {
                    $value = trim($value);
                    if ($value === '') $value = null;
                }

                // Cap varchar columns to 255
                if ($value !== null && in_array($col, [
                    'tipe',
                    'merk',
                    'jenis_produk',
                    'pendaftar',
                    'pabrik',
                    'sub_kategori',
                    'kelompok_produk',
                    'kelas_resiko',
                ])) {
                    $value = mb_substr($value, 0, 255);
                }

                $record[$col] = $value;
            }

            if (!empty($record['nomor_izin_edar'])) {
                $batch[] = $record;
            }
        }

        if (empty($batch)) {
            return response()->json([
                'success'   => true,
                'imported'  => 0,
                'message'   => 'Tidak ada data valid untuk diimport.',
            ]);
        }

        DB::table('izin_edars')->upsert(
            $batch,
            ['nomor_izin_edar'],
            array_diff($allColumns, ['nomor_izin_edar'])
        );

        return response()->json([
            'success'  => true,
            'imported' => count($batch),
            'message'  => count($batch) . ' baris berhasil diimport.',
        ]);
    }

    /**
     * Delete all izin edar records for a specific kategori.
     * DELETE /api/izin-edars/kategori/{kategori}
     */
    public function deleteByKategori(string $kategori): JsonResponse
    {
        $allowed = ['AKD', 'AKL', 'PKD', 'PKL'];

        if (!in_array(strtoupper($kategori), $allowed)) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak valid. Pilihan: ' . implode(', ', $allowed),
            ], 422);
        }

        $kategori = strtoupper($kategori);
        $count = DB::table('izin_edars')->where('kategori', $kategori)->count();

        if ($count === 0) {
            return response()->json([
                'success' => true,
                'deleted' => 0,
                'message' => "Tidak ada data {$kategori} yang ditemukan.",
            ]);
        }

        DB::table('izin_edars')->where('kategori', $kategori)->delete();

        Log::info("[IzinEdar] Deleted {$count} records for kategori {$kategori}");

        return response()->json([
            'success' => true,
            'deleted' => $count,
            'message' => "{$count} data {$kategori} berhasil dihapus.",
        ]);
    }

    /**
     * Download the Excel file for a specific kategori.
     * GET /api/izin-edars/files/{kategori}/download
     */
    public function downloadFile(string $kategori): JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $kategori = strtoupper($kategori);
        $path = $this->syncService->getFilePath($kategori);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak valid.',
            ], 422);
        }

        if (!File::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => "File {$kategori} belum di-download. Silakan sync terlebih dahulu.",
            ], 404);
        }

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Delete the Excel file for a specific kategori from storage.
     * DELETE /api/izin-edars/files/{kategori}
     */
    public function deleteFile(string $kategori): JsonResponse
    {
        $kategori = strtoupper($kategori);
        $path = $this->syncService->getFilePath($kategori);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak valid.',
            ], 422);
        }

        if (!File::exists($path)) {
            return response()->json([
                'success' => true,
                'message' => "File {$kategori} sudah tidak ada.",
            ]);
        }

        $this->syncService->deleteFile($kategori);

        Log::info("[IzinEdar] Deleted file {$kategori} from storage");

        return response()->json([
            'success' => true,
            'message' => "File {$kategori} berhasil dihapus.",
        ]);
    }
}
