<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingEstimate;
use Illuminate\Http\Request;

class ShippingEstimateController extends Controller
{
    public function index(Request $request)
    {
        $estimates = ShippingEstimate::with(['items', 'packages', 'rates'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $estimates->map(function ($est) {
            return [
                'id' => $est->id,
                'no_so' => $est->no_so,
                'customer_name' => $est->customer_name,
                'shipping_address' => $est->shipping_address,
                'total_items' => $est->items->count(),
                'total_packages' => $est->packages->count(),
                'total_invoice' => number_format($est->total_invoice_value, 0, ',', '.'),
                'total_weight' => number_format($est->total_weight_actual, 2),
                'created_at' => $est->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show($id)
    {
        $estimate = ShippingEstimate::with(['items', 'packages', 'rates'])->findOrFail($id);

        return $this->sendResponse([
            'estimate' => $estimate,
            'calculated' => [
                'total_invoice_value' => $estimate->total_invoice_value,
                'total_weight_actual' => $estimate->total_weight_actual,
                'total_weight_dimension_reg' => $estimate->total_weight_dimension_reg,
                'total_weight_dimension_darat' => $estimate->total_weight_dimension_darat,
            ],
            'rates_calculated' => $estimate->rates->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'shipper_name' => $rate->shipper_name,
                    'shipping_type' => $rate->shipping_type,
                    'charged_weight' => $rate->charged_weight,
                    'shipping_cost' => $rate->shipping_cost,
                    'insurance_cost' => $rate->insurance_cost,
                    'total_cost' => $rate->total_cost,
                ];
            }),
        ]);
    }

    public function destroy($id)
    {
        $estimate = ShippingEstimate::findOrFail($id);
        $estimate->delete();

        return $this->sendResponse(null, 'Data berhasil dihapus');
    }

    public function destroyBatch(Request $request)
    {
        $ids = $request->input('ids', []);
        ShippingEstimate::whereIn('id', $ids)->delete();

        return $this->sendResponse(null, 'Data berhasil dihapus');
    }
}
