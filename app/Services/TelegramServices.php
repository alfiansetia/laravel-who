<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class TelegramServices
{
    public static string $base_url = 'https://api.telegram.org/';

    public function __construct() {}

    public static function get_token()
    {
        return config('services.telegram.token');
    }

    public static function send($chat_id, $message)
    {
        $token  = static::get_token();
        $url = static::$base_url . "bot$token/sendMessage";
        $post = Http::post($url, [
            'chat_id'   => $chat_id,
            'text'      => $message
        ]);
        // if (!$post->successful()) {
        //     $post->throw();
        // }
        return $post->json();
    }
}
