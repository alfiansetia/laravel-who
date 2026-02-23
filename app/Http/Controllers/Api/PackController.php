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

    public function index()
    {
        $data = Pack::query()->with(['vendor', 'product', 'items'])->get();
        return $this->sendResponse($data);
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
