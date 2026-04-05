<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPltbb extends Model
{

    protected $guarded = [];

    protected $appends = ['is_complete'];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'p' => 'decimal:2',
        'l' => 'decimal:2',
        't' => 'decimal:2',
        'b' => 'decimal:2',
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
