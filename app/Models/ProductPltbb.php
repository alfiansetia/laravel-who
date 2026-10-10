<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPltbb extends Model
{
    protected $fillable = ['product_id', 'p', 'l', 't', 'b', 'note'];

    protected $appends = ['is_complete'];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'p' => 'float',
        'l' => 'float',
        't' => 'float',
        'b' => 'float',
    ];

    public function getIsCompleteAttribute()
    {
        return $this->p > 0 && $this->l > 0 && $this->t > 0 && $this->b > 0;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
