<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $guarded = ['id'];

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['name'])) {
            $query->where('name', $filters['name']);
        }
        if (isset($filters['desc'])) {
            $query->where('desc', $filters['desc']);
        }
    }

    public function packs()
    {
        return $this->hasMany(Pack::class);
    }
}
