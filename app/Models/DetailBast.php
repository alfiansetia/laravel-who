<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBast extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['bast_id'])) {
            $query->where('bast_id', $filters['bast_id']);
        }
    }

    public function bast()
    {
        return $this->belongsTo(Bast::class, 'bast_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
