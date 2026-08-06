<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PackController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
        $this->middleware('env_auth')->only(['update', 'change', 'destroy', 'destroy_batch']);
    }

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 25), 200);
        $page = max((int) $request->input('page', 1), 1);

        $query = Pack::query()->with(['vendor', 'product']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('desc', 'like', "%{$keyword}%")
                    ->orWhere('vendor_desc', 'like', "%{$keyword}%")
                    ->orWhereHas('product', function ($q2) use ($keyword) {
                        $q2->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('vendor', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', "%{$keyword}%");
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
        $data = Pack::query()->with(['vendor', 'product', 'items'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'          => 'required|string|max:200',
            'desc'          => 'nullable|string|max:200',
            'vendor_desc'   => 'nullable|string|max:200',
            'product_id'    => 'required|exists:products,id',
            'vendor_id'     => 'required|exists:vendors,id',
            'items'         => 'nullable|array',
            'items.*.item'  => 'required_with:items|string|max:65535',
            'items.*.qty'   => 'nullable|string|max:200',
        ]);
        $pack = Pack::create([
            'name'          => $request->name,
            'desc'          => $request->desc,
            'vendor_desc'   => $request->vendor_desc,
            'product_id'    => $request->product_id,
            'vendor_id'     => $request->vendor_id,
        ]);
        if (!empty($request->items)) {
            $items = collect($request->items)->map(function ($item) {
                return [
                    'item' => $item['item'] ?? null,
                    'qty'  => $item['qty'] ?? null,
                ];
            })->toArray();
            $pack->items()->createMany($items);
        }
        return $this->sendResponse($pack, 'Created!');
    }

    public function update(Request $request, $id)
    {
        $pack = Pack::find($id);
        if (!$pack) {
            return $this->sendNotFound();
        }
        $this->validate($request, [
            'name'          => 'required|string|max:200',
            'desc'          => 'nullable|string|max:200',
            'vendor_desc'   => 'nullable|string|max:200',
            'product_id'    => 'required|exists:products,id',
            'vendor_id'     => 'required|exists:vendors,id',
            'items'         => 'nullable|array',
            'items.*.item'  => 'required_with:items|string|max:65535',
            'items.*.qty'   => 'nullable|string|max:200',
        ]);
        $pack->update([
            'name'          => $request->name,
            'desc'          => $request->desc,
            'vendor_desc'   => $request->vendor_desc,
            'product_id'    => $request->product_id,
            'vendor_id'     => $request->vendor_id,
        ]);
        $pack->items()->delete();
        if (!empty($request->items)) {
            $items = collect($request->items)->map(function ($item) {
                return [
                    'item' => $item['item'] ?? null,
                    'qty'  => $item['qty'] ?? null,
                ];
            })->toArray();
            $pack->items()->createMany($items);
        }
        return $this->sendResponse($pack, 'Updated!');
    }

    public function destroy($id)
    {
        $pack = Pack::find($id);
        if (!$pack) {
            return $this->sendNotFound();
        }
        $pack->delete();
        return $this->sendResponse($pack, 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:packs,id',
        ]);
        $deleted = Pack::whereIn('id', $request->ids)->delete();
        return $this->sendResponse(['deleted_count' => $deleted], 'Pack deleted successfully.');
    }

    public function change(Request $request)
    {
        $this->validate($request, [
            'vendor_id' => 'required|exists:vendors,id',
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:packs,id',
        ]);
        $updated = Pack::whereIn('id', $request->ids)
            ->update(['vendor_id' => $request->vendor_id]);

        return $this->sendResponse([
            'updated_count' => $updated
        ], 'Vendor changed successfully.');
    }

    public function download($id)
    {
        $pack = Pack::find($id);
        if (!$pack) {
            return $this->sendNotFound();
        }

        try {
            $path = $this->excelService->generatePack($pack);
            return response()->download($path)->deleteFileAfterSend();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
