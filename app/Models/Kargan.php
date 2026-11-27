<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kargan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function getDefaultPicAttribute()
    {
        return 'Karim Ash Shidik';
    }

    public static function getDefaultMasaAttribute()
    {
        return '1 Tahun ( Unit Utama )';
    }

    public static function generateNameFile($name_file)
    {
        return str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $name_file);
    }

    public static function generateNumber()
    {
        return static::generateNumbersForDate(date('Y-m-d'), 1)[0];
    }

    /**
     * Bangun $count nomor berurutan untuk tanggal $dateYmd (Y-m-d).
     * Prefix roman/tahun mengikuti tanggal (samakan refreshNumber di form),
     * sequence tahunan lanjut dari record terakhir (reset tiap tahun).
     * Tanpa write DB — aman untuk preview maupun bulk import.
     *
     * @return string[]
     */
    public static function generateNumbersForDate(string $dateYmd, int $count): array
    {
        $count = max(1, min($count, 200));
        try {
            $dt = new \DateTimeImmutable($dateYmd);
        } catch (\Throwable) {
            $dt = new \DateTimeImmutable(date('Y-m-d'));
        }
        $month = (int) $dt->format('n');
        $year = $dt->format('Y');

        $last = self::latest()->first();
        $seq = 1;
        if ($last) {
            $explode = explode('/', (string) $last->number);
            if (count($explode) == 4 && $explode[2] == $year) {
                $seq = intval($explode[3]) + 1;
            }
        }

        $month_romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $roman_month = $month_romans[$month] ?? $month_romans[(int) date('n')];

        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $out[] = 'SUPP-GAR/'.$roman_month.'/'.$year.'/'.str_pad($seq + $i, 3, '0', STR_PAD_LEFT);
        }

        return $out;
    }
}
