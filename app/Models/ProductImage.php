<?php

namespace App\Models;

use App\Services\ProductImageStorage;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['url'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            if ($image->name) {
                ProductImageStorage::delete($image->name);
            }
        });
    }

    public function getUrlAttribute()
    {
        return ProductImageStorage::url($this->name);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
