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

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 25), 200);
        $page = max((int) $request->input('page', 1), 1);

        // Subquery: get latest SOP id per product_id
        $query = Sop::query()
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')->from('sops')->groupBy('product_id');
            })
            ->with(['product']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('target', 'like', "%{$keyword}%")
                    ->orWhereHas('product', function ($q2) use ($keyword) {
                        $q2->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    });
            });
        }

        $total = (clone $query)->count();

        $data = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
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
        $sop = Sop::updateOrCreate(
            ['product_id' => $request->product_id],
            ['target' => $request->target]
        );

        $sop->items()->delete();

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
