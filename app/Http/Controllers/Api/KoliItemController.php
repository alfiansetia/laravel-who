<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KoliItem;
use Illuminate\Http\Request;

class KoliItemController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'koli_id'       => 'required|exists:kolis,id',
            'product_id'    => 'required|exists:products,id',
            'qty'           => 'nullable|string',
            'lot'           => 'nullable|string',
            'desc'          => 'nullable|string',
        ]);

        $lastOrder = KoliItem::where('koli_id', $request->koli_id)->max('order') ?? -1;

        $param = [
            'koli_id'       => $request->koli_id,
            'product_id'    => $request->product_id,
            'qty'           => $request->qty,
            'lot'           => $request->lot,
            'desc'          => $request->desc,
            'order'         => $lastOrder + 1,
        ];

        $item = KoliItem::create($param);
        return $this->sendResponse($item->load('product'), 'Item created!');
    }

    public function update(Request $request, KoliItem $koliItem)
    {
        $this->validate($request, [
            'qty'   => 'nullable|string',
            'lot'   => 'nullable|string',
            'desc'  => 'nullable|string',
        ]);

        $param = [
            'qty'   => $request->qty,
            'lot'   => $request->lot,
            'desc'  => $request->desc,
        ];

        $koliItem->update($param);
        return $this->sendResponse($koliItem->load('product'), 'Item updated!');
    }

    public function show(KoliItem $koliItem)
    {
        $koliItem->load(['product', 'koli']);
        return $this->sendResponse($koliItem, '');
    }

    public function destroy(KoliItem $koliItem)
    {
        $koliItem->delete();
        return $this->sendResponse($koliItem, 'Item deleted!');
    }

    public function order(Request $request, KoliItem $koliItem)
    {
        $this->validate($request, [
            'type' => 'required|in:up,down',
        ]);

        $currentOrder = $koliItem->order;
        $koliId = $koliItem->koli_id;

        if ($request->type === 'up') {
            $swapItem = KoliItem::where('koli_id', $koliId)
                ->where('order', '<', $currentOrder)
                ->orderBy('order', 'desc')
                ->first();

            if ($swapItem) {
                $tempOrder = $swapItem->order;
                $swapItem->update(['order' => $currentOrder]);
                $koliItem->update(['order' => $tempOrder]);
            }
        } else {
            $swapItem = KoliItem::where('koli_id', $koliId)
                ->where('order', '>', $currentOrder)
                ->orderBy('order', 'asc')
                ->first();

            if ($swapItem) {
                $tempOrder = $swapItem->order;
                $swapItem->update(['order' => $currentOrder]);
                $koliItem->update(['order' => $tempOrder]);
            }
        }

        return $this->sendResponse($koliItem, 'Order updated!');
    }

    public function clearLot(KoliItem $koliItem)
    {
        $koliItem->update(['lot' => null]);
        return $this->sendResponse($koliItem, 'Lot cleared!');
    }
}
