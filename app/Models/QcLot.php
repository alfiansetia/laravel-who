<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcLot extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id'            => 'integer',
        'product_id'    => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
