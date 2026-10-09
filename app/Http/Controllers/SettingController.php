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
        $session = OdooSession::getCurrentSession();
        $session['session_id'] = $request->env_value;
        OdooSession::saveSession($session);

        if ($request->wantsJson()) {
            return $this->sendResponse('Success!');
        }

        return back()->with('success', 'Success!');
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
        $res = Odoo::getProfile();

        if ($request->wantsJson()) {
            return $this->sendResponse($res, 'Success!');
        }

        return back()->with('success', 'Success!');
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
        $logs = getFolderSize(storage_path('logs'));
        $log_content = '';
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            $log_content = file_get_contents($logPath);
        }

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
                'value' => $logs,
                'parse' => formatBytes($logs),
                'content' => $log_content,
            ],
        ]);
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
