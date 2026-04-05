<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPltbb extends Model
{

    protected $guarded = [];

    protected $appends = ['is_complete'];

    protected $casts = [
        'id'            => 'integer',
        'product_id'    => 'integer',
        'p'             => 'float',
        'l'             => 'float',
        't'             => 'float',
        'b'             => 'float',
    ];

    public function getIsCompleteAttribute()
    {
        return $this->p > 0 && $this->l > 0 && $this->t > 0 && $this->b > 0;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
