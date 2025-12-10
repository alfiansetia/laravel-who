<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShippingEstimateItem extends Model
{
    protected $fillable = [
        'shipping_estimate_id',
        'item_name',
        'quantity',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
    ];

    public function shippingEstimate(): BelongsTo
    {
        return $this->belongsTo(ShippingEstimate::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(
            ShippingEstimatePackage::class,
            'shipping_estimate_package_items',
            'item_id',
            'package_id'
        )->withPivot('quantity')->withTimestamps();
    }
}
