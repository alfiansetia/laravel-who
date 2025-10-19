<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index()
    {
        $data = Sop::query()->with(['product', 'items'])->get()->unique('product_id')->values();
        return $this->sendResponse($data);
    }

    public function show($id)
    {
        $data = Sop::query()->with(['product', 'items'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id'    => 'required|exists:products,id',
            'target'        => 'required|string|max:200',
            'items'         => 'array|min:1',
            'items.*.item'  => 'required_with:items|string|max:200',
        ]);
        Sop::query()->filter($request->only(['product_id']))->delete();
        $target = Sop::create([
            'product_id'    => $request->product_id,
            'target'        => $request->target,
        ]);
        if ($request->has('items')) {
            $target->items()->createMany(
                collect($request->items)->map(fn($i) => ['item' => $i['item']])->toArray()
            );
        }
        return $this->sendResponse('Success');
    }
}
