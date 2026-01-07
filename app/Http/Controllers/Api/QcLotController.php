<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\QcLot;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QcLotController extends Controller
{
    public function index()
    {
        $data = QcLot::query()
            ->with('product')
            ->latest('qc_date')
            ->get();
        return $this->sendResponse($data, 'QcLot retrieved successfully');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'lot_number' => 'required|max:200',
            'lot_expiry' => 'required|max:200',
            'qc_date'   => 'required|date_format:Y-m-d',
            'qc_by'     => 'required|max:200',
            'qc_result' => 'required|max:200',
            'qc_note'   => 'required|max:200',
        ]);
        $qcLot = QcLot::create([
            'product_id' => $request->product_id,
            'lot_number' => $request->lot_number,
            'lot_expiry' => $request->lot_expiry,
            'qc_date'    => $request->qc_date,
            'qc_by'      => $request->qc_by,
            'qc_result'  => $request->qc_result,
            'qc_note'    => $request->qc_note,
        ]);
        return $this->sendResponse($qcLot, 'QcLot created successfully');
    }

    public function show($id)
    {
        $qcLot = QcLot::query()
            ->with('product')
            ->findOrFail($id);
        return $this->sendResponse($qcLot, 'QcLot retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $qcLot = QcLot::query()
            ->with('product')
            ->findOrFail($id);
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'lot_number' => 'required|max:200',
            'lot_expiry' => 'required|max:200',
            'qc_date'   => 'required|date_format:Y-m-d',
            'qc_by'     => 'required|max:200',
            'qc_result' => 'required|max:200',
            'qc_note'   => 'required|max:200',
        ]);
        $qcLot->update([
            'product_id' => $request->product_id,
            'lot_number' => $request->lot_number,
            'lot_expiry' => $request->lot_expiry,
            'qc_date'    => $request->qc_date,
            'qc_by'      => $request->qc_by,
            'qc_result'  => $request->qc_result,
            'qc_note'    => $request->qc_note,
        ]);
        return $this->sendResponse($qcLot, 'QcLot updated successfully');
    }

    public function destroy($id)
    {
        $qcLot = QcLot::query()
            ->with('product')
            ->findOrFail($id);
        $qcLot->delete();
        return $this->sendResponse($qcLot, 'QcLot deleted successfully');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:qc_lots,id',
        ]);
        $deleted = QcLot::whereIn('id', $request->ids)->delete();
        return $this->sendResponse([
            'deleted_count' => $deleted
        ], 'QcLot deleted successfully.');
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'data'              => 'required|array',
            'data.*'            => 'array',
            'data.*.product'    => 'required',
            'data.*.lot'        => 'required|max:200',
            'data.*.ed'         => 'nullable|max:200',
            'data.*.date'       => 'required|date_format:Y-m-d',
            'data.*.qc_by'      => 'nullable|max:200',
            'data.*.qc_note'    => 'nullable|max:200',
        ]);
        try {
            DB::beginTransaction();
            $qcLots = [];
            foreach ($request->data ?? [] as $key => $item) {
                $product = Product::query()->where('code', $item['product'])->first();
                if (!$product) {
                    throw new Exception('Product not found: ' . $item['product']);
                }
                $qcLots[] = QcLot::create([
                    'product_id' => $product->id,
                    'lot_number' => $item['lot'],
                    'lot_expiry' => $item['ed'],
                    'qc_date'    => $item['date'],
                    'qc_by'      => $item['qc_by'] ?? null,
                    'qc_note'    => $item['qc_note'] ?? null,
                ]);
            }
            DB::commit();
            return $this->sendResponse($qcLots, 'QcLot created successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError($th->getMessage());
        }
    }
}
