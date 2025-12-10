<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TelegramServices
{
    public static string $base_url = 'https://api.telegram.org/';

    public function __construct() {}

    public static function getToken()
    {
        return config('services.telegram.token');
    }

    public static function getEnabled(): bool
    {
        return config('services.telegram.enabled');
    }

    public static function getGroupId()
    {
        return config('services.telegram.group');
    }

    public static function sendToGroup($message)
    {
        $group_id = static::getGroupId();
        return static::send($group_id, $message);
    }

    public static function send($chat_id, $message)
    {
        try {
            if (!static::getEnabled()) {
                return true;
            }

            $token = static::getToken();

            if (empty($token)) {
                Log::warning('Telegram: Token tidak tersedia');
                return false;
            }

            $url = static::$base_url . "bot$token/sendMessage";

            // Kirim request dengan timeout 10 detik
            $post = Http::timeout(10)->post($url, [
                'chat_id' => $chat_id,
                'text'    => $message
            ]);

            // Cek apakah request berhasil
            if ($post->successful()) {
                return $post->json();
            } else {
                Log::warning('Telegram: Gagal mengirim pesan', [
                    'chat_id' => $chat_id,
                    'status_code' => $post->status(),
                    'response' => $post->body()
                ]);
                return false;
            }
        } catch (Exception $e) {
            // Log error tanpa mengganggu proses lain
            Log::error('Telegram: Exception saat mengirim pesan', [
                'chat_id' => $chat_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
