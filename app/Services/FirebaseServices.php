<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Http;

class FirebaseServices
{
    public static function getAccessToken()
    {
        $key = config('services.firebase.private_key');
        $credentialsFilePath = storage_path($key);
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        $access_token = $token['access_token'] ?? '';
        return $access_token;
    }

    public static function send($title, $body)
    {
        $tokens = FcmToken::all();
        if ($tokens->isEmpty()) {
            return true;
        }
        $access_token = static::getAccessToken();
        foreach ($tokens as  $token) {
            $param['message'] = [
                'token'     => $token->token,
                'notification'  => [
                    "title" => $title,
                    "body"  => $body,
                ],
            ];
            $proj = config('services.firebase.project_id');
            $apiurl = "https://fcm.googleapis.com/v1/projects/$proj/messages:send";
            $headers = [
                "Authorization" => "Bearer $access_token",
                "Content-Type"  => "application/json",
            ];
            $post = Http::withHeaders($headers)->asJson()->post($apiurl, $param);
            if (!$post->successful()) {
                $token->delete();
            }
        }

        return true;
    }
}
