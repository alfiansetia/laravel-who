<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramServices
{
    public static string $base_url = 'https://api.telegram.org/';

    public function __construct() {}

    public static function getToken()
    {
        return config('services.telegram.token');
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
        $token  = static::getToken();
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
