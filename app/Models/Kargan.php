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

    public static function generateNumber()
    {
        $last = self::latest()->first();
        $number = 1;

        if ($last) {
            $explode = explode('/', $last->number);
            if (count($explode) == 4) {
                if ($explode[2] == date('Y')) {
                    $number = intval($explode[3]) + 1;
                }
            }
        }

        $month_romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $roman_month = $month_romans[date('n')];
        $year = date('Y');
        $number_formatted = str_pad($number, 3, '0', STR_PAD_LEFT);

        return "SUPP-GAR/$roman_month/$year/$number_formatted";
    }
}
