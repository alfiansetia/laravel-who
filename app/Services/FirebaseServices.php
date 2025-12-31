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
            $tokens = FcmToken::all();

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
            $successCount = 0;
            $failedCount = 0;

            foreach ($tokens as $token) {
                try {
                    $param['message'] = [
                        'token' => $token->token,
                        'data'  => [
                            "title" => $title,
                            "body"  => $body,
                            "icon"  => asset('images/asa.png'),
                            'so_id' => $so_id,
                            'url'   => route('so.print', $so_id),
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
                        $successCount++;
                    } else {
                        $failedCount++;
                        Log::warning('Firebase: Gagal mengirim notifikasi, token dihapus', [
                            'token_id' => $token->id,
                            'status_code' => $post->status(),
                            'response' => $post->body()
                        ]);
                        $token->delete();
                    }
                } catch (Exception $e) {
                    // Jangan biarkan 1 token error menghentikan pengiriman ke token lain
                    $failedCount++;
                    Log::error('Firebase: Exception saat mengirim ke token', [
                        'token_id' => $token->id,
                        'error' => $e->getMessage()
                    ]);
                    // Hapus token yang bermasalah
                    $token->delete();
                }
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
