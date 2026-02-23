<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use App\Services\ExcelService;
use Illuminate\Http\Request;

class SopController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
        $this->middleware('env_auth')->only(['store']);
    }

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
            'items.*.item'  => 'required_with:items|string|max:65535',
        ]);
        Sop::query()->filter($request->only(['product_id']))->delete();
        $sop = Sop::create([
            'product_id'    => $request->product_id,
            'target'        => $request->target,
        ]);
        if ($request->has('items')) {
            $sop->items()->createMany(
                collect($request->items)->map(fn($i) => ['item' => $i['item']])->toArray()
            );
        }
        return $this->sendResponse($sop, 'Success');
    }

    public function download($id)
    {
        $sop = Sop::find($id);
        if (!$sop) {
            return $this->sendNotFound();
        }

        $product = $sop->product;
        if (!$product) {
            return $this->sendNotFound();
        }

        try {
            $path = $this->excelService->generateSop($product);
            return response()->download($path)->deleteFileAfterSend();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
