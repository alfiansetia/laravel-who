<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackItem extends Model
{
    protected $guarded = ['id'];

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['pack_id'])) {
            $query->where('pack_id', $filters['pack_id']);
        }
    }

    public function pack()
    {
        return $this->belongsTo(Pack::class);
    }
}
