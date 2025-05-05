<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackingList;
use Illuminate\Http\Request;

class PackingListController extends Controller
{
    public function index()
    {
        $data = PackingList::query()->with('product')->get()->unique('product_id')->values();
        return response()->json(['data' => $data], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product'   => 'required|exists:products,id',
            'items'     => 'array|min:1',
        ]);
        PackingList::query()->where('product_id', $request->product)->delete();
        foreach ($request->items ?? [] as $key => $item) {
            $pl = PackingList::create([
                'product_id' => $request->product,
                'item'      => $item['item'],
                'qty'       => $item['qty'],
            ]);
        }
        return response()->json(['message' => 'Success'], 200);
    }
}
