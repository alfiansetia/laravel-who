<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailBast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetailBastController extends Controller
{

    public function index(Request $request)
    {
        $data = DetailBast::query()
            ->with('product')
            ->filter($request->only(['bast_id']))
            ->orderBy('order', 'asc')
            ->get();
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'bast'      => 'required|exists:basts,id',
            'product'   => 'required|exists:products,id',
            'qty'       => 'required',
            'lot'       => 'nullable',
            'satuan'    => 'required|in:Pcs,Pck,Unit,EA,Box,Btl,Vial',
        ]);

        $lastOrder = DetailBast::where('bast_id', $request->bast)->max('order') ?? -1;

        $data = DetailBast::create([
            'bast_id'       => $request->bast,
            'product_id'    => $request->product,
            'qty'           => $request->qty,
            'lot'           => $request->lot,
            'satuan'        => $request->satuan,
            'order'         => $lastOrder + 1,
        ]);
        return $this->sendResponse($data, 'Created!');
    }

    public function show(DetailBast $detail_bast)
    {
        $data = $detail_bast->load('product');
        return $this->sendResponse($data);
    }

    public function update(Request $request, DetailBast $detail_bast)
    {
        $this->validate($request, [
            'qty'       => 'required',
            'lot'       => 'nullable',
            'satuan'    => 'required|in:Pcs,Pck,Unit,EA,Box,Btl,Vial',
        ]);
        $detail_bast->update([
            'qty'       => $request->qty,
            'lot'       => $request->lot,
            'satuan'    => $request->satuan,
        ]);
        return $this->sendResponse($detail_bast, 'Updated!');
    }

    public function destroy(DetailBast $detail_bast)
    {
        $detail_bast->delete();
        return $this->sendResponse($detail_bast, 'Deleted!');
    }

    public function order(Request $request, DetailBast $detail_bast)
    {
        $this->validate($request, [
            'type' => 'required|in:up,down',
        ]);

        try {
            DB::beginTransaction();

            $bastId = $detail_bast->bast_id;

            // Re-index all items to ensure unique sequential orders
            $items = DetailBast::where('bast_id', $bastId)
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate() // Lock rows to prevent race conditions
                ->get();

            foreach ($items as $index => $item) {
                $item->update(['order' => $index]);
            }

            // Refresh current item to get updated order
            $detail_bast->refresh();
            $currentOrder = $detail_bast->order;

            if ($request->type === 'up') {
                $swapItem = DetailBast::where('bast_id', $bastId)
                    ->where('order', '<', $currentOrder)
                    ->orderBy('order', 'desc')
                    ->first();

                if ($swapItem) {
                    $tempOrder = $swapItem->order;
                    $swapItem->update(['order' => $currentOrder]);
                    $detail_bast->update(['order' => $tempOrder]);
                }
            } else {
                $swapItem = DetailBast::where('bast_id', $bastId)
                    ->where('order', '>', $currentOrder)
                    ->orderBy('order', 'asc')
                    ->first();

                if ($swapItem) {
                    $tempOrder = $swapItem->order;
                    $swapItem->update(['order' => $currentOrder]);
                    $detail_bast->update(['order' => $tempOrder]);
                }
            }

            DB::commit();
            return $this->sendResponse($detail_bast->load('product'), 'Order updated!');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError($th->getMessage());
        }
    }
}
