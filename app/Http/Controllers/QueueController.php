<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kelola antrean queue database: daftar job tertunda/gagal,
 * detail payload, retry, lepas (reserved macet), hapus, kosongkan tabel.
 */
class QueueController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth');
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Queue/Index', [
            'title' => 'Queue',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function stats(): JsonResponse
    {
        $pending = (int) DB::table('jobs')->whereNull('reserved_at')->count();
        $reserved = (int) DB::table('jobs')->whereNotNull('reserved_at')->count();
        $failed = (int) DB::table('failed_jobs')->count();

        return $this->sendResponse([
            'pending' => $pending,
            'reserved' => $reserved,
            'failed' => $failed,
            'total' => $pending + $reserved,
            'driver' => (string) config('queue.default'),
        ]);
    }

    public function jobs(Request $request): JsonResponse
    {
        $perPage = max((int) $request->input('per_page', 10), 1);
        $page = max((int) $request->input('page', 1), 1);
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($request->input('search') ?? ''));

        $query = DB::table('jobs');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('id')->skip($offset)->take($perPage)->get();

        $data = $rows->map(fn ($r) => $this->describeJob((array) $r))->values();

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
        ]);
    }

    public function failed(Request $request): JsonResponse
    {
        $perPage = max((int) $request->input('per_page', 10), 1);
        $page = max((int) $request->input('page', 1), 1);
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($request->input('search') ?? ''));

        $query = DB::table('failed_jobs');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%")
                    ->orWhere('exception', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('failed_at')->skip($offset)->take($perPage)->get();

        $data = $rows->map(fn ($r) => $this->describeFailed((array) $r))->values();

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
        ]);
    }

    public function jobShow(int $id): JsonResponse
    {
        $row = DB::table('jobs')->where('id', $id)->first();
        if (! $row) {
            return $this->sendError('Job tidak ditemukan.', 404);
        }

        $info = $this->describeJob((array) $row);
        $payload = $this->decodePayload($row->payload ?? '');
        $info['command_props'] = $this->commandProps($payload['data']['command'] ?? null);
        $info['payload'] = $this->safePayload($payload);

        return $this->sendResponse($info);
    }

    public function failedShow(string $uuid): JsonResponse
    {
        $row = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        if (! $row) {
            return $this->sendError('Failed job tidak ditemukan.', 404);
        }

        $info = $this->describeFailed((array) $row, false);
        $payload = $this->decodePayload($row->payload ?? '');
        $info['command_props'] = $this->commandProps($payload['data']['command'] ?? null);
        $info['payload'] = $this->safePayload($payload);
        $info['exception'] = (string) ($row->exception ?? '');

        return $this->sendResponse($info);
    }

    public function retry(string $uuid): JsonResponse
    {
        $row = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        if (! $row) {
            return $this->sendError('Failed job tidak ditemukan.', 404);
        }

        try {
            $code = Artisan::call('queue:retry', ['id' => [$uuid]]);
            $output = trim((string) Artisan::output());
        } catch (\Throwable $e) {
            return $this->sendError('Gagal retry: '.$e->getMessage());
        }

        if ($code !== 0) {
            return $this->sendError($output !== '' ? $output : 'Retry gagal.', 422);
        }

        return $this->sendResponse(null, 'Job di-retry, masuk antrean lagi.');
    }

    /**
     * Lepaskan job yang macet berstatus reserved (worker mati)
     * agar bisa dikerjakan ulang worker.
     */
    public function release(int $id): JsonResponse
    {
        $row = DB::table('jobs')->where('id', $id)->first();
        if (! $row) {
            return $this->sendError('Job tidak ditemukan.', 404);
        }

        DB::table('jobs')->where('id', $id)->update([
            'reserved_at' => null,
            'attempts' => 0,
        ]);

        return $this->sendResponse(null, 'Job dilepas, siap dikerjakan worker.');
    }

    public function destroyJob(int $id): JsonResponse
    {
        $deleted = DB::table('jobs')->where('id', $id)->delete();
        if (! $deleted) {
            return $this->sendError('Job tidak ditemukan.', 404);
        }

        return $this->sendResponse(null, 'Job dihapus dari antrean.');
    }

    public function destroyFailed(string $uuid): JsonResponse
    {
        $deleted = DB::table('failed_jobs')->where('uuid', $uuid)->delete();
        if (! $deleted) {
            return $this->sendError('Failed job tidak ditemukan.', 404);
        }

        return $this->sendResponse(null, 'Failed job dihapus.');
    }

    public function flushJobs(Request $request): JsonResponse
    {
        $query = DB::table('jobs');
        $queue = trim((string) ($request->input('queue') ?? ''));
        if ($queue !== '') {
            $query->where('queue', $queue);
        }
        $count = $query->delete();

        return $this->sendResponse(['deleted' => $count], "{$count} job dihapus dari antrean.");
    }

    public function flushFailed(): JsonResponse
    {
        $count = DB::table('failed_jobs')->delete();

        return $this->sendResponse(['deleted' => $count], "{$count} failed job dihapus.");
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function describeJob(array $row): array
    {
        $payload = $this->decodePayload($row['payload'] ?? '');
        $class = $this->commandClass($payload['data']['command'] ?? null);

        return [
            'id' => $row['id'] ?? null,
            'queue' => $row['queue'] ?? 'default',
            'job' => $class ? class_basename($class) : ($payload['displayName'] ?? '-'),
            'job_class' => $class,
            'attempts' => (int) ($row['attempts'] ?? 0),
            'reserved' => ($row['reserved_at'] ?? null) !== null,
            'reserved_at' => $this->ts($row['reserved_at'] ?? null),
            'available_at' => $this->ts($row['available_at'] ?? null),
            'created_at' => $this->ts($row['created_at'] ?? null),
        ];
    }

    private function describeFailed(array $row, bool $excerpt = true): array
    {
        $payload = $this->decodePayload($row['payload'] ?? '');
        $class = $this->commandClass($payload['data']['command'] ?? null);
        $exception = (string) ($row['exception'] ?? '');

        return [
            'id' => $row['id'] ?? null,
            'uuid' => $row['uuid'] ?? null,
            'queue' => $row['queue'] ?? 'default',
            'connection' => $row['connection'] ?? null,
            'job' => $class ? class_basename($class) : ($payload['displayName'] ?? '-'),
            'job_class' => $class,
            'failed_at' => $this->dbTs($row['failed_at'] ?? null),
            'error' => $excerpt ? mb_substr(trim(strtok($exception, "\n")), 0, 160) : $exception,
        ];
    }

    private function decodePayload(mixed $raw): array
    {
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Payload aman ditampilkan: command serialized diganti ringkasan
     * (detail properti ada di `command_props`).
     */
    private function safePayload(array $payload): array
    {
        if (isset($payload['data']['command']) && is_string($payload['data']['command'])) {
            $payload['data']['command'] = '[serialized '.strlen($payload['data']['command']).' bytes]';
        }

        return $payload;
    }

    private function commandClass(mixed $command): ?string
    {
        if (! is_string($command) || $command === '') {
            return null;
        }
        if (preg_match('/^O:\d+:"([^"]+)"/', $command, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Properti publik command job (tanpa unserialize objek asing:
     * hanya untuk payload antrean milik aplikasi sendiri).
     *
     * @return array<string, mixed>|null
     */
    private function commandProps(mixed $command): ?array
    {
        if (! is_string($command) || $command === '') {
            return null;
        }
        try {
            $obj = unserialize($command, ['allowed_classes' => true]);
        } catch (\Throwable) {
            return null;
        }
        if (! is_object($obj)) {
            return null;
        }
        // Hanya properti yang dideklarasikan di class job itu sendiri
        // (properti bawaan trait antrean seperti connection/chain disembunyikan).
        $ref = new \ReflectionClass($obj);
        $props = [];
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $p) {
            if ($p->getDeclaringClass()->getName() !== $ref->getName()) {
                continue;
            }
            if ($p->isStatic()) {
                continue;
            }
            // Payload lama (diserial sebelum properti baru ditambahkan)
            // menghasilkan typed property yang belum diinisialisasi —
            // getValue() melempar Error untuk kasus itu.
            try {
                $props[$p->getName()] = $p->isInitialized($obj)
                    ? $this->shorten($p->getValue($obj))
                    : null;
            } catch (\Throwable) {
                $props[$p->getName()] = null;
            }
        }

        return $props;
    }

    private function shorten(mixed $value): mixed
    {
        if (is_string($value) && mb_strlen($value) > 200) {
            return mb_substr($value, 0, 200).'…';
        }
        if (is_array($value)) {
            $out = [];
            foreach (array_slice($value, 0, 20, true) as $k => $v) {
                $out[$k] = $this->shorten($v);
            }

            return $out;
        }
        if (is_object($value)) {
            return 'object('.get_class($value).')';
        }

        return $value;
    }

    private function ts(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::createFromTimestamp((int) $value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function dbTs(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
