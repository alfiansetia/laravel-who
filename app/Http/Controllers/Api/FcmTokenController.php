<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Services\FirebaseServices;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function index(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
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
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'token'     => 'required',
            'topic'     => 'nullable',
            'platform'  => 'nullable',
        ]);
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        $topic = $request->input('topic', 'general') ?: 'general';
        $token = FcmToken::query()->updateOrCreate(
            [
                'token' => $request->token,
            ],
            [
                'token'         => $request->token,
                'topic'         => $topic,
                'user_agent'    => $userAgent,
                'ip'            => $ip,
                'platform'      => $request->platform,
            ]
        );

        // Best-effort: daftarkan token ke topic agar sendToTopic() menjangkaunya.
        // Kegagalan subscribe tidak menggagalkan penyimpanan token.
        FirebaseServices::subscribeTopic($token->token, $topic);

        return $this->sendResponse($token, 'Success Upsert Token');
    }

    public function show(FcmToken $token)
    {
        return $this->sendResponse($token, 'Success Get Token');
    }

    /**
     * Tes push notif FCM ke SATU token milik pemanggil (perangkat ini).
     * Sengaja tanpa env_auth agar tiap user bisa cek notifikasinya sendiri,
     * tanpa mem-broadcast ke semua perangkat.
     */
    public function test(Request $request)
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
            'last_status'    => $result['ok'] ? 'SUCCESS' : ($result['error'] ?? 'FAILED'),
            'last_status_at' => now(),
        ]);

        if (! $result['ok']) {
            return $this->sendError('Gagal kirim: ' . ($result['error'] ?? 'unknown'), 422);
        }

        return $this->sendResponse(null, 'Test notif dikirim ke perangkat ini!');
    }

    public function destroy(FcmToken $token)
    {
        // Best-effort: keluarkan dari topic sebelum baris DB dihapus.
        if (! empty($token->topic)) {
            FirebaseServices::unsubscribeTopic($token->token, $token->topic);
        }

        $token->delete();
        return $this->sendResponse($token, 'Success Delete Token');
    }
}
