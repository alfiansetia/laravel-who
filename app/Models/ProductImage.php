<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['url'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            $path = 'products/' . $image->name;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    public function getUrlAttribute()
    {
        if (! $this->name) {
            return null;
        }
        return asset('storage/products/' . $this->name);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
