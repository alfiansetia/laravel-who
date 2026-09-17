<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan file lampiran AKL di S3 (prefix akl/).
 *
 * Beda dengan ProductImage: tidak ada fallback local (upload langsung S3),
 * dan url() tidak pernah HeadObject (menghindari 403 R2 saat serialisasi).
 */
class AklFileStorage
{
    public const DISK = 's3';

    public const PREFIX = 'akl/';

    public static function key(string $filename): string
    {
        return self::PREFIX.ltrim($filename, '/');
    }

    public static function put(string $filename, string $contents): bool
    {
        return Storage::disk(self::DISK)->put(self::key($filename), $contents, 'public');
    }

    public static function get(string $filename): ?string
    {
        try {
            return Storage::disk(self::DISK)->get(self::key($filename));
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    public static function exists(string $filename): bool
    {
        try {
            return Storage::disk(self::DISK)->exists(self::key($filename));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public static function url(?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }

        return Storage::disk(self::DISK)->url(self::key($filename));
    }

    public static function delete(string $filename): void
    {
        try {
            Storage::disk(self::DISK)->delete(self::key($filename));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function mime(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}
