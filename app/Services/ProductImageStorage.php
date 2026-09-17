<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ProductImageStorage
{
    public const DISK = 's3';

    public const FALLBACK_DISK = 'public';

    public const PREFIX = 'products/';

    public static function key(string $filename): string
    {
        return self::PREFIX.ltrim($filename, '/');
    }

    public static function existsOnS3(string $filename): bool
    {
        try {
            return Storage::disk(self::DISK)->exists(self::key($filename));
        } catch (\Throwable $e) {
            // R2 bisa return 403 HeadObject kalau token kurang permission.
            // Jangan bikin request crash, anggap tidak ada.
            report($e);

            return false;
        }
    }

    public static function existsOnLocal(string $filename): bool
    {
        return Storage::disk(self::FALLBACK_DISK)->exists(self::key($filename));
    }

    public static function exists(string $filename): bool
    {
        return self::existsOnS3($filename) || self::existsOnLocal($filename);
    }

    public static function put(string $filename, string $contents): bool
    {
        return Storage::disk(self::DISK)->put(self::key($filename), $contents, 'public');
    }

    /**
     * Ambil isi file: local dulu (murah, tanpa network), fallback ke S3.
     * S3 dibungkus try/catch karena GetObject bisa 403 kalau token R2
     * tidak punya permission read.
     */
    public static function get(string $filename): ?string
    {
        $key = self::key($filename);

        if (self::existsOnLocal($filename)) {
            return Storage::disk(self::FALLBACK_DISK)->get($key);
        }

        try {
            return Storage::disk(self::DISK)->get($key);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * URL publik TANPA network call ke S3.
     *
     * Kenapa: Storage::disk('s3')->exists() memicu HeadObject ke R2.
     * Kalau token R2 tidak punya permission read, HeadObject 403 dan
     * accessor `url` (dipakai tiap toArray()/JSON) bikin response 500.
     *
     * Aturan:
     * - Selama file local masih ada -> serve local (masa transisi aman).
     * - Setelah sync --delete-local / upload baru (hanya ada di S3) -> URL S3.
     */
    public static function url(?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }

        if (self::existsOnLocal($filename)) {
            return asset('storage/products/'.$filename);
        }

        return Storage::disk(self::DISK)->url(self::key($filename));
    }

    public static function delete(string $filename): void
    {
        $key = self::key($filename);

        try {
            Storage::disk(self::DISK)->delete($key);
        } catch (\Throwable $e) {
            report($e);
        }

        if (Storage::disk(self::FALLBACK_DISK)->exists($key)) {
            Storage::disk(self::FALLBACK_DISK)->delete($key);
        }
    }

    /**
     * Statistik isi prefix products/ di S3 (untuk halaman Server Config).
     * Size diambil dari hasil ListObjects (tanpa HeadObject per file).
     * Dibatasi 10.000 object agar tidak runaway; selebihnya flag truncated.
     */
    public static function s3Stats(): array
    {
        try {
            $files = 0;
            $bytes = 0;
            $truncated = false;

            foreach (Storage::disk(self::DISK)->getDriver()->listContents(self::PREFIX, true) as $item) {
                if (! $item->isFile()) {
                    continue;
                }
                $files++;
                $bytes += $item->fileSize() ?? 0;
                if ($files >= 10000) {
                    $truncated = true;
                    break;
                }
            }

            return ['files' => $files, 'bytes' => $bytes, 'truncated' => $truncated, 'error' => null];
        } catch (\Throwable $e) {
            report($e);

            return ['files' => 0, 'bytes' => 0, 'truncated' => false, 'error' => 'S3 tidak dapat diakses'];
        }
    }
}
