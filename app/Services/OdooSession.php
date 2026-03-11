<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class OdooSession
{
    /**
     * Path ke file session.
     */
    public static function getSessionFile(): string
    {
        return storage_path('app/session.json');
    }

    /**
     * Ambil data session saat ini.
     */
    public static function getCurrentSession(): array
    {
        $path = static::getSessionFile();

        if (!File::exists($path)) {
            return static::setDefaultSession();
        }

        try {
            $json = File::get($path);
            $data = json_decode($json, true);

            // Validasi minimal: harus ada session_id dan uid
            if (empty(Arr::get($data, 'session_id')) || empty(Arr::get($data, 'uid'))) {
                return static::setDefaultSession();
            }

            return $data;
        } catch (\Exception $e) {
            return static::setDefaultSession();
        }
    }

    /**
     * Simpan data session.
     */
    public static function saveSession(array $data): void
    {
        // Pastikan kita menggabungkan dengan default agar tidak ada key yang hilang
        $mergedData = array_merge(static::getDefaultData(), $data);

        File::put(
            static::getSessionFile(),
            json_encode($mergedData, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Reset ke session default/kosong.
     */
    public static function setDefaultSession(): array
    {
        $data = static::getDefaultData();

        File::put(
            static::getSessionFile(),
            json_encode($data, JSON_PRETTY_PRINT)
        );

        return $data;
    }

    /**
     * Struktur data default.
     */
    protected static function getDefaultData(): array
    {
        return [
            'session_id'            => null,
            'uid'                   => 0,
            'db'                    => null,
            'name'                  => null,
            'username'              => null,
            'partner_display_name'  => null,
            'partner_id'            => 0,
        ];
    }
}
