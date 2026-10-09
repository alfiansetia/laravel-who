<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailBast extends Model
{
    use HasFactory;

    protected $fillable = ['bast_id', 'product_id', 'qty', 'satuan', 'lot', 'order'];

    protected $casts = [
        'bast_id' => 'integer',
        'product_id' => 'integer',
        'order' => 'integer',
    ];

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['bast_id'])) {
            $query->where('bast_id', $filters['bast_id']);
        }
    }

    public function bast(): BelongsTo
    {
        return $this->belongsTo(Bast::class, 'bast_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
