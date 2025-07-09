<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class OdooSession
{
    public static function getSessionFile()
    {
        return storage_path('app/session.json');
    }

    public static function getCurrentSession()
    {
        $session_file = static::getSessionFile();
        if (file_exists($session_file)) {
            $json = File::get($session_file);
            $data = json_decode($json, true);
            if (empty($data['session_id']) || empty($data['uid'])) {
                return static::setDefaultSession();
            }
            return $data;
        } else {
            return static::setDefaultSession();
        }
    }

    public static function saveSession($data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $session_file = static::getSessionFile();
        File::put($session_file, $json);
    }

    public static function setDefaultSession()
    {
        $data = [
            'session_id'            => null,
            'uid'                   => 0,
            'db'                    => null,
            'name'                  => null,
            'username'              => null,
            'partner_display_name'  => null,
            'partner_id'            => 0,
        ];
        $session_file = static::getSessionFile();
        File::put($session_file, json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }
}
