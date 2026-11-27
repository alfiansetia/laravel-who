<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlamatBaru extends Model
{
    protected $guarded = ['id'];

    public function kolis()
    {
        return $this->hasMany(Koli::class);
    }

    /**
     * Pecah field `do` gabungan jadi daftar nomor DO.
     * Contoh: "CENT/OUT/18062 & CENT/OUT/18059" → ["CENT/OUT/18062", "CENT/OUT/18059"].
     * Catatan dalam kurung dibuang: "CENT/OUT/17619(EP-...)" → "CENT/OUT/17619".
     * Pemisah `&` dan `,` didukung, duplikat (case-insensitive) dibuang.
     *
     * @return string[]
     */
    public static function parseDoNumbers(?string $do): array
    {
        $text = (string) ($do ?? '');
        $text = (string) preg_replace('/\([^()]*\)/', '', $text);
        $parts = preg_split('/[&,]/', $text) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = (string) preg_replace('/\s+/', ' ', trim((string) $part));
            if ($part === '') {
                continue;
            }
            $key = strtolower($part);
            if (! array_key_exists($key, $out)) {
                $out[$key] = $part;
            }
        }

        return array_values($out);
    }
}
