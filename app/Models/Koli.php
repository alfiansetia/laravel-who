<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Koli extends Model
{
    protected $guarded = ['id'];

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['alamat_baru_id'])) {
            $query->where('alamat_baru_id', $filters['alamat_baru_id']);
        }
    }

    public function alamatBaru()
    {
        return $this->belongsTo(AlamatBaru::class);
    }

    public function items()
    {
        return $this->hasMany(KoliItem::class)->orderBy('order');
    }
}
