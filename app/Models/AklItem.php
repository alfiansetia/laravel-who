<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AklItem extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['product_name', 'is_custom'];

    public function akl()
    {
        return $this->belongsTo(Akl::class);
    }

    /**
     * Referensi ke product via code (bukan FK, boleh custom / di luar master).
     * Jika code tidak ada di products, berarti item custom.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'code', 'code');
    }

    public function getProductNameAttribute(): ?string
    {
        // Hindari N+1 bila sudah di-load via with('product').
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->name;
        }
        // Fallback query ringan bila relasi belum di-load.
        return Product::where('code', $this->code)->value('name');
    }

    public function getIsCustomAttribute(): bool
    {
        if ($this->relationLoaded('product')) {
            return is_null($this->product);
        }
        return ! Product::where('code', $this->code)->exists();
    }
}
