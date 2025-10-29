<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailAlamat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['alamat_id'])) {
            $query->where('alamat_id', $filters['alamat_id']);
        }
    }

    public function alamat()
    {
        return $this->belongsTo(Alamat::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
