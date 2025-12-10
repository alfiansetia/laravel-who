<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingEstimate extends Model
{
    protected $fillable = [
        'no_so',
        'customer_name',
        'shipping_address',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShippingEstimateItem::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ShippingEstimatePackage::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingEstimateRate::class);
    }

    /**
     * Get total invoice value (calculated)
     */
    public function getTotalInvoiceValueAttribute(): float
    {
        return $this->items->sum('total_price');
    }

    /**
     * Get total actual weight (calculated) - multiplied by quantity
     */
    public function getTotalWeightActualAttribute(): float
    {
        return $this->packages->sum(function ($pkg) {
            return $pkg->weight_actual * ($pkg->quantity ?? 1);
        });
    }

    /**
     * Get total dimension weight for REG/UDARA (divisor 6000) - rounded up, multiplied by quantity
     */
    public function getTotalWeightDimensionRegAttribute(): float
    {
        return $this->packages->sum(function ($pkg) {
            $dimWeight = ceil(($pkg->dimension_length * $pkg->dimension_width * $pkg->dimension_height) / 6000);
            return $dimWeight * ($pkg->quantity ?? 1);
        });
    }

    /**
     * Get total dimension weight for DARAT/LAUT (divisor 4000) - rounded up, multiplied by quantity
     */
    public function getTotalWeightDimensionDaratAttribute(): float
    {
        return $this->packages->sum(function ($pkg) {
            $dimWeight = ceil(($pkg->dimension_length * $pkg->dimension_width * $pkg->dimension_height) / 4000);
            return $dimWeight * ($pkg->quantity ?? 1);
        });
    }
}
