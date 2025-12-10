<?php

namespace App\Http\Controllers;

use App\Models\ShippingEstimate;
use App\Models\ShippingEstimateItem;
use App\Models\ShippingEstimatePackage;
use App\Models\ShippingEstimateRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingEstimateController extends Controller
{
    public function index()
    {
        return view('shipping_estimate.index');
    }

    public function create()
    {
        return view('shipping_estimate.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_so' => 'required|string|max:50|unique:shipping_estimates,no_so',
            'customer_name' => 'required|string|max:255',
            'shipping_address' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $estimate = ShippingEstimate::create([
                'no_so' => $request->no_so,
                'customer_name' => $request->customer_name,
                'shipping_address' => $request->shipping_address,
                'created_by' => auth()->id(),
            ]);

            // Save items
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['item_name'])) {
                        $estimate->items()->create([
                            'item_name' => $item['item_name'],
                            'quantity' => $item['quantity'] ?? 1,
                            'total_price' => $item['total_price'] ?? 0,
                        ]);
                    }
                }
            }

            // Save packages
            if ($request->has('packages')) {
                foreach ($request->packages as $index => $pkg) {
                    if (!empty($pkg['package_name']) || !empty($pkg['weight_actual'])) {
                        $estimate->packages()->create([
                            'package_number' => $index + 1,
                            'package_name' => $pkg['package_name'] ?? null,
                            'quantity' => $pkg['quantity'] ?? 1,
                            'weight_actual' => $pkg['weight_actual'] ?? 0,
                            'dimension_length' => $pkg['dimension_length'] ?? 0,
                            'dimension_width' => $pkg['dimension_width'] ?? 0,
                            'dimension_height' => $pkg['dimension_height'] ?? 0,
                        ]);
                    }
                }
            }

            // Save rates
            if ($request->has('rates')) {
                foreach ($request->rates as $rate) {
                    if (!empty($rate['shipper_name'])) {
                        $estimate->rates()->create([
                            'shipper_name' => $rate['shipper_name'],
                            'shipping_type' => $rate['shipping_type'] ?? 'DARAT',
                            'rate_per_kg' => $rate['rate_per_kg'] ?? 0,
                            'insurance_percentage' => $rate['insurance_percentage'] ?? 0,
                            'packing_cost' => $rate['packing_cost'] ?? 0,
                            'admin_fee' => $rate['admin_fee'] ?? 0,
                            'ppn_percentage' => $rate['ppn_percentage'] ?? 0,
                            'estimated_days' => $rate['estimated_days'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('shipping_estimate.edit', $estimate->id)
                ->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $estimate = ShippingEstimate::with(['items', 'packages', 'rates'])->findOrFail($id);
        return view('shipping_estimate.show', compact('estimate'));
    }

    public function edit($id)
    {
        $estimate = ShippingEstimate::with(['items', 'packages', 'rates'])->findOrFail($id);
        return view('shipping_estimate.edit', compact('estimate'));
    }

    public function update(Request $request, $id)
    {
        $estimate = ShippingEstimate::findOrFail($id);

        $request->validate([
            'no_so' => 'required|string|max:50|unique:shipping_estimates,no_so,' . $id,
            'customer_name' => 'required|string|max:255',
            'shipping_address' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $estimate->update([
                'no_so' => $request->no_so,
                'customer_name' => $request->customer_name,
                'shipping_address' => $request->shipping_address,
            ]);

            // Sync items
            $estimate->items()->delete();
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['item_name'])) {
                        $estimate->items()->create([
                            'item_name' => $item['item_name'],
                            'quantity' => $item['quantity'] ?? 1,
                            'total_price' => $item['total_price'] ?? 0,
                        ]);
                    }
                }
            }

            // Sync packages
            $estimate->packages()->delete();
            if ($request->has('packages')) {
                foreach ($request->packages as $index => $pkg) {
                    if (!empty($pkg['package_name']) || !empty($pkg['weight_actual'])) {
                        $estimate->packages()->create([
                            'package_number' => $index + 1,
                            'package_name' => $pkg['package_name'] ?? null,
                            'quantity' => $pkg['quantity'] ?? 1,
                            'weight_actual' => $pkg['weight_actual'] ?? 0,
                            'dimension_length' => $pkg['dimension_length'] ?? 0,
                            'dimension_width' => $pkg['dimension_width'] ?? 0,
                            'dimension_height' => $pkg['dimension_height'] ?? 0,
                        ]);
                    }
                }
            }

            // Sync rates
            $estimate->rates()->delete();
            if ($request->has('rates')) {
                foreach ($request->rates as $rate) {
                    if (!empty($rate['shipper_name'])) {
                        $estimate->rates()->create([
                            'shipper_name' => $rate['shipper_name'],
                            'shipping_type' => $rate['shipping_type'] ?? 'DARAT',
                            'rate_per_kg' => $rate['rate_per_kg'] ?? 0,
                            'insurance_percentage' => $rate['insurance_percentage'] ?? 0,
                            'packing_cost' => $rate['packing_cost'] ?? 0,
                            'admin_fee' => $rate['admin_fee'] ?? 0,
                            'ppn_percentage' => $rate['ppn_percentage'] ?? 0,
                            'estimated_days' => $rate['estimated_days'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('shipping_estimate.edit', $estimate->id)
                ->with('success', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $estimate = ShippingEstimate::findOrFail($id);
        $estimate->delete();

        return redirect()->route('shipping_estimate.index')
            ->with('success', 'Data berhasil dihapus');
    }
}
