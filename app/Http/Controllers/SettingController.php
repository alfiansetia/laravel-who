<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use App\Services\FirebaseServices;
use App\Services\Odoo;
use App\Services\OdooSession;
use App\Services\ProductImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->except([
            'tokenIndex', 'tokenStore', 'tokenShow', 'tokenTest', 'tokenDestroy',
            'resourceIndex', 'cek_odoo',
        ]);
    }

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            $session = OdooSession::getCurrentSession();

            return $this->sendResponse($session);
        }

        return Inertia::render('Setting/Index', [
            'title' => 'App Setting',
            'session' => OdooSession::getCurrentSession(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'env_value' => 'required',
        ]);
        $sessionId = trim((string) $request->env_value);
        if ($sessionId === '') {
            if ($request->wantsJson()) {
                return $this->sendError('Session ID wajib diisi.', 422);
            }

            return back()->with('error', 'Session ID wajib diisi.');
        }

        $session = OdooSession::getCurrentSession();
        $session['session_id'] = $sessionId;
        OdooSession::saveSession($session);

        // Sinkronisasi identitas (uid/nama/username) dari profil Odoo agar
        // session tempel manual tidak menyimpan uid basi. Best-effort:
        // session tetap tersimpan walau Odoo tidak bisa dihubungi.
        $verified = $this->syncSessionIdentity();

        $message = $verified
            ? 'Session tersimpan dan terverifikasi ke Odoo.'
            : 'Session tersimpan, tapi identitas belum terverifikasi (Odoo tidak merespons).';

        if ($request->wantsJson()) {
            return $this->sendResponse(['verified' => $verified], $message);
        }

        return back()->with('success', $message);
    }

    private function syncSessionIdentity(): bool
    {
        try {
            // Resolusi identitas dari session_id-nya sendiri, bukan dari
            // uid basi yang tersimpan (session tempel manual bisa milik
            // user berbeda). Gagal = session tetap tersimpan (best-effort).
            $res = Odoo::getSessionInfo();
        } catch (\Throwable) {
            return false;
        }

        $info = is_array($res) ? ($res['result'] ?? null) : null;
        if (! is_array($info) || empty($info['uid'])) {
            return false;
        }

        OdooSession::updateIdentityFromSessionInfo($info);

        return true;
    }

    public function reload(Request $request): JsonResponse|RedirectResponse
    {
        Artisan::call('app:odoo-login');

        if ($request->wantsJson()) {
            return $this->sendResponse('Success!');
        }

        return back()->with('success', 'Success!');
    }

    public function test_notif(Request $request): JsonResponse|RedirectResponse
    {
        FirebaseServices::sendToTopic('⚠️ Test!', 'Eh yaampun ini cuma test notif 😁✌️!');

        if ($request->wantsJson()) {
            return $this->sendResponse('Success kirim notif via topic ke semua perangkat!');
        }

        return back()->with('success', 'Success kirim notif via topic ke semua perangkat!');
    }

    public function cek_odoo(Request $request): JsonResponse|RedirectResponse
    {
        $started = microtime(true);
        $session = OdooSession::getCurrentSession();

        try {
            $res = Odoo::getProfile();
            $latency = (int) round((microtime(true) - $started) * 1000);
            $payload = $this->summarizeOdooProfile($res, true, $latency, null);

            if ($request->wantsJson()) {
                return $this->sendResponse($payload, 'Koneksi Odoo OK.');
            }

            return back()->with('success', 'Koneksi Odoo OK.');
        } catch (\Throwable $e) {
            $latency = (int) round((microtime(true) - $started) * 1000);
            $payload = $this->summarizeOdooProfile(null, false, $latency, $e->getMessage(), method_exists($e, 'getContext') ? $e->getContext() : []);

            if ($request->wantsJson()) {
                return $this->sendResponse($payload, 'Koneksi Odoo gagal.');
            }

            return back()->with('error', 'Koneksi Odoo gagal: '.$e->getMessage());
        }
    }

    private function summarizeOdooProfile(mixed $res, bool $ok, int $latencyMs, ?string $error, mixed $errorContext = null): array
    {
        $session = OdooSession::getCurrentSession();
        $record = null;

        if (is_array($res)) {
            $result = $res['result'] ?? null;
            if (is_array($result)) {
                $record = array_is_list($result) ? ($result[0] ?? null) : $result;
            }
        }

        $user = null;
        if (is_array($record)) {
            $company = $record['company_id'] ?? null;
            $user = [
                'id' => $record['id'] ?? ($session['uid'] ?? 0),
                'name' => $record['name'] ?? ($session['name'] ?? '-'),
                'display_name' => $record['display_name'] ?? ($record['name'] ?? '-'),
                'email' => $record['email'] ?? ($session['username'] ?? '-'),
                'lang' => $record['lang'] ?? '-',
                'tz' => $record['tz'] ?? '-',
                'tz_offset' => $record['tz_offset'] ?? '-',
                'company_id' => is_array($company) ? ($company[0] ?? null) : $company,
                'company_name' => is_array($company) ? ($company[1] ?? '-') : '-',
                'notification_type' => $record['notification_type'] ?? '-',
                'last_update' => $record['__last_update'] ?? '-',
                'has_avatar' => ! empty($record['image']),
            ];
        }

        return [
            'ok' => $ok,
            'latency_ms' => $latencyMs,
            'checked_at' => now()->toDateTimeString(),
            'error' => $error,
            'error_context' => $errorContext,
            'session' => [
                'uid' => $session['uid'] ?? 0,
                'db' => $session['db'] ?? '-',
                'name' => $session['name'] ?? '-',
                'username' => $session['username'] ?? '-',
                'partner_display_name' => $session['partner_display_name'] ?? '-',
                'partner_id' => $session['partner_id'] ?? 0,
                'session_id' => $session['session_id'] ?? '',
                'session_short' => $this->maskSessionId($session['session_id'] ?? ''),
            ],
            'user' => $user,
            'raw' => $res,
        ];
    }

    private function maskSessionId(?string $sessionId): string
    {
        $s = (string) ($sessionId ?? '');
        if (strlen($s) <= 12) {
            return $s !== '' ? str_repeat('•', strlen($s)) : '-';
        }

        return substr($s, 0, 6).'…'.substr($s, -4);
    }

    public function tokenIndex(Request $request): JsonResponse
    {
        $draw = $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $query = FcmToken::query();

        $recordsTotal = $query->count();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('platform', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%")
                    ->orWhere('ip', 'like', "%{$search}%")
                    ->orWhere('token', 'like', "%{$search}%")
                    ->orWhere('last_status', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'platform', 'user_agent', 'ip', 'token', 'last_status'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $query->orderBy($orderColumn, $orderDir);

        $data = $query->skip($start)->take($length > 0 ? $length : 10)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function tokenStore(Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'token' => 'required',
            'topic' => 'nullable',
            'platform' => 'nullable',
        ]);
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        $topic = $request->input('topic', 'general') ?: 'general';
        $token = FcmToken::query()->updateOrCreate(
            [
                'token' => $request->token,
            ],
            [
                'token' => $request->token,
                'topic' => $topic,
                'user_agent' => $userAgent,
                'ip' => $ip,
                'platform' => $request->platform,
            ]
        );

        // Best-effort: daftarkan token ke topic agar sendToTopic() menjangkaunya.
        // Kegagalan subscribe tidak menggagalkan penyimpanan token.
        FirebaseServices::subscribeTopic($token->token, $topic);

        if ($request->wantsJson()) {
            return $this->sendResponse($token, 'Success Upsert Token');
        }

        return back()->with('success', 'Success Upsert Token');
    }

    public function tokenShow(Request $request, FcmToken $token): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return $this->sendResponse($token, 'Success Get Token');
        }

        return redirect()->route('settings.index');
    }

    /**
     * Tes push notif FCM ke SATU token milik pemanggil (perangkat ini).
     * Sengaja tanpa env_auth agar tiap user bisa cek notifikasinya sendiri,
     * tanpa mem-broadcast ke semua perangkat.
     */
    public function tokenTest(Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'token' => 'required',
        ]);

        $result = FirebaseServices::sendToToken(
            $request->token,
            '⚠️ Test!',
            'Eh yaampun ini cuma test notif 😁✌️!'
        );

        // Catat hasilnya agar kolom Last Status di halaman setting tetap hidup
        // meski pengiriman broadcast sudah pindah ke topic.
        FcmToken::where('token', $request->token)->update([
            'last_status' => $result['ok'] ? 'SUCCESS' : ($result['error'] ?? 'FAILED'),
            'last_status_at' => now(),
        ]);

        if (! $result['ok']) {
            if ($request->wantsJson()) {
                return $this->sendError('Gagal kirim: '.($result['error'] ?? 'unknown'), 422);
            }

            return back()->with('error', 'Gagal kirim: '.($result['error'] ?? 'unknown'));
        }

        if ($request->wantsJson()) {
            return $this->sendResponse(null, 'Test notif dikirim ke perangkat ini!');
        }

        return back()->with('success', 'Test notif dikirim ke perangkat ini!');
    }

    public function tokenDestroy(Request $request, FcmToken $token): JsonResponse|RedirectResponse
    {
        // Best-effort: keluarkan dari topic sebelum baris DB dihapus.
        if (! empty($token->topic)) {
            FirebaseServices::unsubscribeTopic($token->token, $token->topic);
        }

        $token->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($token, 'Success Delete Token');
        }

        return back()->with('success', 'Success Delete Token');
    }

    public function resourceIndex(Request $request): JsonResponse
    {
        // Produk kini primer di S3; local hanya sisa yang belum di-sync.
        $s3 = ProductImageStorage::s3Stats();
        $localProducts = getFolderSize(storage_path('app/public/products'));
        $logFiles = $this->listLogFiles();
        $logsTotal = array_sum(array_column($logFiles, 'size'));

        return $this->sendResponse([
            'products' => [
                'files' => $s3['files'],
                'value' => $s3['bytes'],
                'parse' => formatBytes($s3['bytes']),
                'truncated' => $s3['truncated'],
                'error' => $s3['error'],
            ],
            'products_local' => [
                'value' => $localProducts,
                'parse' => formatBytes($localProducts),
            ],
            'logs' => [
                'value' => $logsTotal,
                'parse' => formatBytes($logsTotal),
                'count' => count($logFiles),
                'files' => $logFiles,
            ],
        ]);
    }

    public function logShow(Request $request, string $file): JsonResponse
    {
        $path = $this->resolveLogPath($file);
        if ($path === null) {
            return $this->sendError('File log tidak ditemukan.', 404);
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', 25);
        $perPage = min(max($perPage, 10), 100);
        $search = trim((string) $request->input('search', ''));
        $level = strtoupper(trim((string) $request->input('level', '')));

        [$content, $truncated] = $this->readLogTail($path, 2 * 1024 * 1024);
        $entries = $this->parseLogEntries($content);

        $levels = [];
        foreach ($entries as $entry) {
            $lv = $entry['level'];
            $levels[$lv] = ($levels[$lv] ?? 0) + 1;
        }
        ksort($levels);

        if ($level !== '' && $level !== 'ALL') {
            $entries = array_values(array_filter($entries, function ($e) use ($level) {
                return $e['level'] === $level;
            }));
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $entries = array_values(array_filter($entries, function ($e) use ($needle) {
                return mb_strpos(mb_strtolower($e['timestamp'].' '.$e['level'].' '.$e['message'].' '.$e['preview']), $needle) !== false;
            }));
        }

        // Entri terbaru dulu.
        $entries = array_reverse($entries);

        $total = count($entries);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $pageEntries = array_slice($entries, ($page - 1) * $perPage, $perPage);

        $lines = explode("\n", $content);
        $rawTail = implode("\n", array_slice($lines, -150));
        if (strlen($rawTail) > 60 * 1024) {
            $rawTail = substr($rawTail, -60 * 1024);
        }

        return $this->sendResponse([
            'file' => $this->describeLogFile($path),
            'truncated' => $truncated,
            'levels' => $levels,
            'entries' => $pageEntries,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
            'raw_tail' => $rawTail,
            'filters' => ['search' => $search, 'level' => $level],
        ]);
    }

    public function logClear(Request $request, string $file): JsonResponse|RedirectResponse
    {
        $path = $this->resolveLogPath($file);
        if ($path === null) {
            if ($request->wantsJson()) {
                return $this->sendError('File log tidak ditemukan.', 404);
            }

            return back()->with('error', 'File log tidak ditemukan.');
        }

        file_put_contents($path, '');

        if ($request->wantsJson()) {
            return $this->sendResponse(null, 'Log '.basename($path).' berhasil dikosongkan.');
        }

        return back()->with('success', 'Log berhasil dikosongkan.');
    }

    public function logDestroy(Request $request, string $file): JsonResponse|RedirectResponse
    {
        $path = $this->resolveLogPath($file);
        if ($path === null) {
            if ($request->wantsJson()) {
                return $this->sendError('File log tidak ditemukan.', 404);
            }

            return back()->with('error', 'File log tidak ditemukan.');
        }

        @unlink($path);

        if ($request->wantsJson()) {
            return $this->sendResponse(null, 'Log '.basename($path).' berhasil dihapus.');
        }

        return back()->with('success', 'Log berhasil dihapus.');
    }

    private function logDirectory(): string
    {
        return storage_path('logs');
    }

    private function resolveLogPath(string $file): ?string
    {
        $base = basename($file);
        if ($base === '' || ! str_ends_with($base, '.log')) {
            return null;
        }
        $path = $this->logDirectory().DIRECTORY_SEPARATOR.$base;
        if (! is_file($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @return array<int, array{name: string, size: int, parse: string, modified: string, modified_at: int, lines: ?int}>
     */
    private function listLogFiles(): array
    {
        $dir = $this->logDirectory();
        $paths = glob($dir.DIRECTORY_SEPARATOR.'*.log') ?: [];
        $files = [];

        foreach ($paths as $path) {
            $files[] = $this->describeLogFile($path);
        }

        usort($files, function ($a, $b) {
            return $b['modified_at'] <=> $a['modified_at'];
        });

        return $files;
    }

    /**
     * @return array{name: string, size: int, parse: string, modified: string, modified_at: int, lines: ?int}
     */
    private function describeLogFile(string $path): array
    {
        $size = is_file($path) ? (int) filesize($path) : 0;
        $mtime = is_file($path) ? (int) filemtime($path) : 0;
        $lines = null;
        if ($size > 0 && $size <= 2 * 1024 * 1024) {
            $raw = @file_get_contents($path);
            if (is_string($raw)) {
                $lines = substr_count($raw, "\n") + 1;
            }
        }

        return [
            'name' => basename($path),
            'size' => $size,
            'parse' => formatBytes($size),
            'modified' => $mtime > 0 ? date('d M Y H:i:s', $mtime) : '-',
            'modified_at' => $mtime,
            'lines' => $lines,
        ];
    }

    /**
     * Baca ekor file agar file besar (mis. daily log menumpuk) tidak memenuhi memori.
     *
     * @return array{0: string, 1: bool}
     */
    private function readLogTail(string $path, int $maxBytes): array
    {
        $size = (int) filesize($path);
        if ($size <= 0) {
            return ['', false];
        }

        if ($size <= $maxBytes) {
            return [(string) file_get_contents($path), false];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ['', false];
        }
        fseek($handle, -$maxBytes, SEEK_END);
        $chunk = (string) stream_get_contents($handle);
        fclose($handle);

        // Buang baris pertama yang kemungkinan terpotong.
        $pos = strpos($chunk, "\n");
        if ($pos !== false) {
            $chunk = substr($chunk, $pos + 1);
        }

        return [$chunk, true];
    }

    /**
     * Pecah isi log Laravel menjadi entri readable: [tanggal] env.LEVEL: pesan + konteks/stack.
     *
     * @return array<int, array{id: int, timestamp: string, env: string, level: string, message: string, preview: string, raw: string, has_stack: bool}>
     */
    private function parseLogEntries(string $content): array
    {
        if (trim($content) === '') {
            return [];
        }

        $chunks = preg_split('/(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/m', $content) ?: [];
        $entries = [];
        $id = 0;

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }

            if (! preg_match('/^\[(?P<ts>[^\]]+)\]\s+(?P<env>[^.]+)\.(?P<level>\w+):\s*(?P<body>.*)$/s', $chunk, $m)) {
                continue;
            }

            $lines = explode("\n", $m['body']);
            $firstLine = trim($lines[0] ?? '');
            $message = $firstLine;
            $context = '';

            // Potong ekor JSON konteks (mis. {"exception": ...}) agar pesan readable.
            $exceptionPos = strpos($firstLine, '{"exception"');
            if ($exceptionPos === false) {
                $exceptionPos = strpos($firstLine, ' {"');
            }
            if ($exceptionPos !== false && $exceptionPos > 0) {
                $maybeContext = trim(substr($firstLine, $exceptionPos));
                if (str_starts_with($maybeContext, '{')) {
                    $context = $maybeContext;
                    $message = trim(substr($firstLine, 0, $exceptionPos));
                }
            } elseif (preg_match('/^(?P<msg>.*?)(?P<json>\{.*\})\s*$/s', $firstLine, $jm)) {
                $maybeMsg = trim($jm['msg']);
                if ($maybeMsg !== '') {
                    $message = $maybeMsg;
                }
                $context = trim($jm['json']);
            }

            if (mb_strlen($message) > 300) {
                $message = mb_substr($message, 0, 300).'…';
            }

            $rest = trim(implode("\n", array_slice($lines, 1)));
            $previewSource = $context !== '' ? $context : $rest;
            $preview = mb_substr(trim(preg_replace('/\s+/', ' ', $previewSource) ?? ''), 0, 220);
            $raw = mb_substr($chunk, 0, 6000);

            $id++;
            $entries[] = [
                'id' => $id,
                'timestamp' => $m['ts'],
                'env' => $m['env'],
                'level' => strtoupper($m['level']),
                'message' => $message !== '' ? $message : '(tanpa pesan)',
                'preview' => $preview,
                'raw' => $raw,
                'has_stack' => $rest !== '',
            ];
        }

        return $entries;
    }

    public function resourceDestroyLog(Request $request): JsonResponse|RedirectResponse
    {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');

            if ($request->wantsJson()) {
                return $this->sendResponse(null, 'Log berhasil dikosongkan.');
            }

            return back()->with('success', 'Log berhasil dikosongkan.');
        }

        if ($request->wantsJson()) {
            return response()->json(null);
        }

        return back()->with('success', 'Log berhasil dikosongkan.');
    }
}
