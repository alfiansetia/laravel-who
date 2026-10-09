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
            // Jangan hapus session_id yang tersimpan hanya karena uid kosong:
            // reset hanya bila tidak ada session sama sekali.
            if (! is_array($data) || empty($data['session_id'])) {
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

    /**
     * Selaraskan uid/nama/partner tersimpan dengan identitas pemilik
     * session_id saat ini (hasil /web/session/get_session_info).
     * Dipakai setelah tempel session manual agar uid basi tidak
     * menimpa/menggagalkan verifikasi berikutnya.
     */
    public static function updateIdentityFromSessionInfo(array $info)
    {
        $session = static::getCurrentSession();
        if (! empty($info['session_id']) && is_string($info['session_id'])) {
            $session['session_id'] = $info['session_id'];
        }
        if (isset($info['uid'])) {
            $session['uid'] = (int) $info['uid'];
        }
        if (array_key_exists('db', $info) && $info['db'] !== null && $info['db'] !== '') {
            $session['db'] = $info['db'];
        }
        foreach (['name', 'username', 'partner_display_name'] as $key) {
            if (array_key_exists($key, $info) && $info[$key] !== null && $info[$key] !== '') {
                $session[$key] = $info[$key];
            }
        }
        if (isset($info['partner_id'])) {
            $session['partner_id'] = (int) $info['partner_id'];
        }
        static::saveSession($session);

        return $session;
    }

    public static function setDefaultSession()
    {
        $data = [
            'session_id' => null,
            'uid' => 0,
            'db' => null,
            'name' => null,
            'username' => null,
            'partner_display_name' => null,
            'partner_id' => 0,
        ];
        $session_file = static::getSessionFile();
        File::put($session_file, json_encode($data, JSON_PRETTY_PRINT));

        return $data;
    }
}
