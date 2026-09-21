<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class FirebaseServices
{
    /**
     * Cache key untuk menyimpan access token
     */
    private static string $cacheKey = 'firebase_access_token';

    /**
     * Durasi cache dalam menit (50 menit, token valid 60 menit)
     */
    private static int $cacheDuration = 50;

    public static function getAccessToken()
    {
        try {
            // Cek apakah token sudah ada di cache
            $cachedToken = Cache::get(self::$cacheKey);

            if (!empty($cachedToken)) {
                return $cachedToken;
            }

            // Jika tidak ada di cache, generate token baru
            $key = config('services.firebase.private_key');

            if (empty($key)) {
                Log::warning('Firebase: Private key tidak dikonfigurasi');
                return null;
            }

            $credentialsFilePath = storage_path($key);

            if (!file_exists($credentialsFilePath)) {
                Log::error('Firebase: File credentials tidak ditemukan', [
                    'path' => $credentialsFilePath
                ]);
                return null;
            }

            $client = new \Google_Client();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $token = $client->getAccessToken();
            $access_token = $token['access_token'] ?? '';

            if (empty($access_token)) {
                Log::warning('Firebase: Access token kosong');
                return null;
            }

            // Simpan token ke cache untuk 50 menit
            Cache::put(self::$cacheKey, $access_token, now()->addMinutes(self::$cacheDuration));

            return $access_token;
        } catch (Exception $e) {
            Log::error('Firebase: Exception saat mendapatkan access token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Hapus access token dari cache (untuk testing atau force refresh)
     */
    public static function clearAccessToken()
    {
        Cache::forget(self::$cacheKey);
    }

    public static function send($title, $body, $so_id = 0)
    {
        try {
            // Ambil id => token saja, tanpa hydrate full model per baris.
            $tokens = FcmToken::query()->pluck('token', 'id');

            if ($tokens->isEmpty()) {
                return true;
            }

            $access_token = static::getAccessToken();

            if (empty($access_token)) {
                Log::warning('Firebase: Tidak bisa mengirim notifikasi, access token tidak tersedia');
                return false;
            }

            $proj = config('services.firebase.project_id');

            if (empty($proj)) {
                Log::warning('Firebase: Project ID tidak dikonfigurasi');
                return false;
            }

            $apiurl = "https://fcm.googleapis.com/v1/projects/$proj/messages:send";
            $successIds = [];
            $failedGroups = []; // label error => [ids], untuk 1x bulk update per label
            $unregisteredIds = [];

            foreach ($tokens as $id => $token) {
                try {
                    $param['message'] = [
                        'token' => $token,
                        'data'  => [
                            "title" => (string) $title,
                            "body"  => (string) $body,
                            "icon"  => (string) asset('images/asa.png'),
                            'so_id' => (string) $so_id,
                            'url'   => (string) route('so.print', $so_id),
                        ],
                    ];

                    $headers = [
                        "Authorization" => "Bearer $access_token",
                        "Content-Type"  => "application/json",
                    ];

                    // Kirim request dengan timeout 10 detik
                    $post = Http::timeout(10)
                        ->withHeaders($headers)
                        ->asJson()
                        ->post($apiurl, $param);

                    if ($post->successful()) {
                        $successIds[] = $id;
                    } else {
                        $errorStatus = $post->json('error.status');
                        $errorCode = $post->json('error.details.0.errorCode');

                        Log::warning('Firebase: Gagal mengirim notifikasi', [
                            'token_id' => $id,
                            'status_code' => $post->status(),
                            'error_status' => $errorStatus,
                            'error_code' => $errorCode,
                            'response' => $post->body()
                        ]);

                        $label = $errorCode ?? $errorStatus ?? 'FAILED';
                        $failedGroups[$label][] = $id;

                        // Hanya hapus jika token memang sudah tidak terdaftar (UNREGISTERED)
                        if ($errorStatus === 'UNREGISTERED' || $errorCode === 'UNREGISTERED') {
                            $unregisteredIds[] = $id;
                            Log::info("Firebase: Token ID {$id} dihapus karena UNREGISTERED");
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Firebase: Exception saat mengirim ke token', [
                        'token_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                    // Jangan menghapus token jika terjadi exception (misal: timeout/koneksi)
                    // Status lama dibiarkan, tidak ditulis ulang.
                }
            }

            // Bulk update status: 1 query per kelompok, bukan 1 save per token.
            $stampedAt = now();
            if (! empty($successIds)) {
                FcmToken::whereIn('id', $successIds)
                    ->update(['last_status' => 'SUCCESS', 'last_status_at' => $stampedAt]);
            }
            foreach ($failedGroups as $label => $ids) {
                FcmToken::whereIn('id', $ids)
                    ->update(['last_status' => $label, 'last_status_at' => $stampedAt]);
            }
            if (! empty($unregisteredIds)) {
                FcmToken::whereIn('id', $unregisteredIds)->delete();
            }

            return true;
        } catch (Exception $e) {
            Log::error('Firebase: Exception saat mengirim notifikasi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'title' => $title
            ]);
            return false;
        }
    }

    /**
     * Normalisasi nama topic sesuai aturan FCM: [a-zA-Z0-9-_.~%]+
     */
    public static function sanitizeTopic(string $topic): string
    {
        $topic = ltrim(trim($topic), '/topics/');
        $topic = preg_replace('/[^a-zA-Z0-9\-_.~%]+/', '-', $topic);

        return substr($topic, 0, 900);
    }

    /**
     * Daftarkan satu / banyak token ke sebuah topic via IID API.
     * Dipakai agar sendToTopic() bisa menjangkau token tersebut.
     * Best-effort: return false jika gagal, tanpa exception ke caller.
     *
     * @param string|array $tokens
     */
    public static function subscribeTopic($tokens, string $topic = 'general'): bool
    {
        return static::manageTopicSubscription($tokens, $topic, 'batchAdd');
    }

    /**
     * Hapus satu / banyak token dari sebuah topic via IID API.
     *
     * @param string|array $tokens
     */
    public static function unsubscribeTopic($tokens, string $topic = 'general'): bool
    {
        return static::manageTopicSubscription($tokens, $topic, 'batchRemove');
    }

    /**
     * Kirim notifikasi ke sebuah topic: 1x HTTP request untuk semua subscriber.
     * Payload data disamakan dengan send() agar SW & foreground handler
     * yang sudah ada tetap jalan tanpa perubahan.
     */
    public static function sendToTopic($title, $body, $so_id = 0, string $topic = 'general')
    {
        try {
            $topic = static::sanitizeTopic($topic);

            if ($topic === '') {
                Log::warning('Firebase: Nama topic kosong, pengiriman dibatalkan');
                return false;
            }

            $access_token = static::getAccessToken();

            if (empty($access_token)) {
                Log::warning('Firebase: Tidak bisa mengirim ke topic, access token tidak tersedia');
                return false;
            }

            $proj = config('services.firebase.project_id');

            if (empty($proj)) {
                Log::warning('Firebase: Project ID tidak dikonfigurasi');
                return false;
            }

            $apiurl = "https://fcm.googleapis.com/v1/projects/$proj/messages:send";

            $param['message'] = [
                'topic' => $topic,
                'data'  => [
                    "title" => (string) $title,
                    "body"  => (string) $body,
                    "icon"  => (string) asset('images/asa.png'),
                    'so_id' => (string) $so_id,
                    'url'   => (string) route('so.print', $so_id),
                ],
            ];

            $post = Http::timeout(10)
                ->withHeaders([
                    "Authorization" => "Bearer $access_token",
                    "Content-Type"  => "application/json",
                ])
                ->asJson()
                ->post($apiurl, $param);

            if ($post->successful()) {
                return true;
            }

            Log::warning('Firebase: Gagal mengirim notifikasi ke topic', [
                'topic'       => $topic,
                'status_code' => $post->status(),
                'response'    => $post->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Firebase: Exception saat mengirim ke topic', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'title' => $title,
                'topic' => $topic ?? null,
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi ke SATU token (untuk tes ke perangkat ini).
     * Return array [ok => bool, error => ?string] agar controller
     * bisa memberi pesan yang jelas ke user.
     */
    public static function sendToToken($token, $title, $body, $so_id = 0): array
    {
        try {
            if (empty($token)) {
                return ['ok' => false, 'error' => 'Token kosong'];
            }

            $access_token = static::getAccessToken();

            if (empty($access_token)) {
                Log::warning('Firebase: Tidak bisa mengirim ke token, access token tidak tersedia');
                return ['ok' => false, 'error' => 'Access token tidak tersedia'];
            }

            $proj = config('services.firebase.project_id');

            if (empty($proj)) {
                Log::warning('Firebase: Project ID tidak dikonfigurasi');
                return ['ok' => false, 'error' => 'Project ID tidak dikonfigurasi'];
            }

            $apiurl = "https://fcm.googleapis.com/v1/projects/$proj/messages:send";

            $param['message'] = [
                'token' => $token,
                'data'  => [
                    "title" => (string) $title,
                    "body"  => (string) $body,
                    "icon"  => (string) asset('images/asa.png'),
                    'so_id' => (string) $so_id,
                    'url'   => (string) route('so.print', $so_id),
                ],
            ];

            $post = Http::timeout(10)
                ->withHeaders([
                    "Authorization" => "Bearer $access_token",
                    "Content-Type"  => "application/json",
                ])
                ->asJson()
                ->post($apiurl, $param);

            if ($post->successful()) {
                return ['ok' => true, 'error' => null];
            }

            $errorStatus = $post->json('error.status');
            $errorCode = $post->json('error.details.0.errorCode');
            $label = $errorCode ?? $errorStatus ?? ('HTTP ' . $post->status());

            Log::warning('Firebase: Gagal mengirim ke single token', [
                'error_status' => $errorStatus,
                'error_code'   => $errorCode,
                'status_code'  => $post->status(),
                'response'     => $post->body(),
            ]);

            return ['ok' => false, 'error' => $label];
        } catch (Exception $e) {
            Log::error('Firebase: Exception saat mengirim ke single token', [
                'error' => $e->getMessage(),
            ]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Helper batchAdd / batchRemove ke IID API, di-chunk 1000 token per request.
     *
     * @param string|array $tokens
     */
    protected static function manageTopicSubscription($tokens, string $topic, string $action): bool
    {
        try {
            $tokens = is_array($tokens) ? array_values(array_filter($tokens)) : [$tokens];
            $tokens = array_values(array_unique(array_filter($tokens)));

            if (empty($tokens)) {
                return true;
            }

            $topic = static::sanitizeTopic($topic);

            if ($topic === '') {
                Log::warning('Firebase: Nama topic kosong, subscribe dibatalkan');
                return false;
            }

            $access_token = static::getAccessToken();

            if (empty($access_token)) {
                Log::warning('Firebase: Tidak bisa subscribe topic, access token tidak tersedia');
                return false;
            }

            $ok = true;

            foreach (array_chunk($tokens, 1000) as $chunk) {
                $post = Http::timeout(10)
                    ->withHeaders([
                        "Authorization" => "Bearer $access_token",
                        "Content-Type"  => "application/json",
                        "access_token_auth" => "true",
                    ])
                    ->asJson()
                    ->post("https://iid.googleapis.com/iid/v1:{$action}", [
                        'to'                  => '/topics/' . $topic,
                        'registration_tokens' => $chunk,
                    ]);

                if (! $post->successful()) {
                    $ok = false;
                    Log::warning('Firebase: Gagal subscribe topic', [
                        'topic'       => $topic,
                        'action'      => $action,
                        'count'       => count($chunk),
                        'status_code' => $post->status(),
                        'response'    => $post->body(),
                    ]);
                }
            }

            return $ok;
        } catch (Exception $e) {
            Log::error('Firebase: Exception saat subscribe topic', [
                'error'  => $e->getMessage(),
                'topic'  => $topic ?? null,
                'action' => $action ?? null,
            ]);
            return false;
        }
    }
}
