<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KoliItem extends Model
{
    protected $guarded = ['id'];

    public function koli()
    {
        return $this->belongsTo(Koli::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
