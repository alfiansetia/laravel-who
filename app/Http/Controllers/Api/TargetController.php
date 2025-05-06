<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Target;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        $data = Target::query()->with(['product', 'items'])->get()->unique('product_id')->values();
        return response()->json(['data' => $data], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product'   => 'required|exists:products,id',
            'target'    => 'required|string|max:200',
            'items'     => 'array|min:1',
        ]);
        Target::query()->where('product_id', $request->product)->delete();
        $target = Target::create([
            'product_id'    => $request->product,
            'target'        => $request->target,
        ]);
        foreach ($request->items ?? [] as $key => $item) {
            $target->items()->create([
                'item'          => $item['item'],
            ]);
        }
        return response()->json(['message' => 'Success'], 200);
    }
}
