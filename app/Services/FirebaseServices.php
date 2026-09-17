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
}
