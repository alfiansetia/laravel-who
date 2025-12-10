<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingEstimateRate extends Model
{
    protected $fillable = [
        'shipping_estimate_id',
        'shipper_name',
        'shipping_type',
        'rate_per_kg',
        'insurance_percentage',
        'packing_cost',
        'admin_fee',
        'ppn_percentage',
        'estimated_days',
    ];

    protected $casts = [
        'rate_per_kg' => 'decimal:2',
        'insurance_percentage' => 'decimal:2',
        'packing_cost' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'ppn_percentage' => 'decimal:2',
    ];

    public function shippingEstimate(): BelongsTo
    {
        return $this->belongsTo(ShippingEstimate::class);
    }

    /**
     * Get divisor based on shipping type
     */
    public function getDivisorAttribute(): int
    {
        return $this->shipping_type === 'REG' ? 6000 : 4000;
    }

    /**
     * Get charged weight (max of actual vs dimension weight)
     */
    public function getChargedWeightAttribute(): float
    {
        $estimate = $this->shippingEstimate;
        $actualWeight = $estimate->total_weight_actual;

        $dimensionWeight = $this->shipping_type === 'REG'
            ? $estimate->total_weight_dimension_reg
            : $estimate->total_weight_dimension_darat;

        return max($actualWeight, $dimensionWeight);
    }

    /**
     * Get shipping cost (calculated)
     */
    public function getShippingCostAttribute(): float
    {
        return $this->charged_weight * $this->rate_per_kg;
    }

    /**
     * Get insurance cost (calculated)
     */
    public function getInsuranceCostAttribute(): float
    {
        $totalInvoice = $this->shippingEstimate->total_invoice_value;
        return $totalInvoice * ($this->insurance_percentage / 100);
    }

    /**
     * Get subtotal before PPN (ongkir + asuransi + packing + admin)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->shipping_cost + $this->insurance_cost + $this->packing_cost + $this->admin_fee;
    }

    /**
     * Get PPN cost (calculated from shipping cost / ongkir)
     */
    public function getPpnCostAttribute(): float
    {
        return $this->shipping_cost * ($this->ppn_percentage / 100);
    }

    /**
     * Get total cost (calculated: subtotal + PPN)
     */
    public function getTotalCostAttribute(): float
    {
        return $this->subtotal + $this->ppn_cost;
    }
}
