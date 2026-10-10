<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $guarded = ['id'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(PackItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function topItems()
    {
        return $this->hasMany(PackItem::class)->whereNull('parent_id')->orderBy('sort_order')->orderBy('id');
    }
}
