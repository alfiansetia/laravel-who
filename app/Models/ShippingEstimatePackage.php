<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShippingEstimatePackage extends Model
{
    protected $fillable = [
        'shipping_estimate_id',
        'package_number',
        'package_name',
        'quantity',
        'weight_actual',
        'dimension_length',
        'dimension_width',
        'dimension_height',
    ];

    protected $casts = [
        'package_number' => 'integer',
        'quantity' => 'integer',
        'weight_actual' => 'decimal:2',
        'dimension_length' => 'decimal:2',
        'dimension_width' => 'decimal:2',
        'dimension_height' => 'decimal:2',
    ];

    public function shippingEstimate(): BelongsTo
    {
        return $this->belongsTo(ShippingEstimate::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            ShippingEstimateItem::class,
            'shipping_estimate_package_items',
            'package_id',
            'item_id'
        )->withPivot('quantity')->withTimestamps();
    }

    /**
     * Get dimension weight for REG/UDARA (divisor 6000)
     */
    public function getWeightDimensionRegAttribute(): float
    {
        return ($this->dimension_length * $this->dimension_width * $this->dimension_height) / 6000;
    }

    /**
     * Get dimension weight for DARAT/LAUT (divisor 4000)
     */
    public function getWeightDimensionDaratAttribute(): float
    {
        return ($this->dimension_length * $this->dimension_width * $this->dimension_height) / 4000;
    }
}
