<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QcLot;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QcLotController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')
            ->only([
                'destroy',
                'destroy_batch',
                'import',
                'update',
                'store',
            ]);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = QcLot::query()->with('product');

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('lot_number', 'like', "%{$keyword}%")
                        ->orWhere('lot_expiry', 'like', "%{$keyword}%")
                        ->orWhere('qc_by', 'like', "%{$keyword}%")
                        ->orWhere('qc_note', 'like', "%{$keyword}%")
                        ->orWhereHas('product', function ($q2) use ($keyword) {
                            $q2->where('code', 'like', "%{$keyword}%")
                                ->orWhere('name', 'like', "%{$keyword}%");
                        });
                });
            }

            $total = (clone $query)->count();

            $data = $query->orderBy('qc_date', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        return Inertia::render('QcLot/Index', [
            'title' => 'QC Lot',
            'filters' => $request->only(['search', 'page']),
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'lot_number' => 'required|max:200',
            'lot_expiry' => 'nullable|max:200',
            'qc_date' => 'required|date_format:Y-m-d',
            'qc_by' => 'required|max:200',
            'qc_note' => 'nullable|max:200',
        ]);
        $qcLot = QcLot::create([
            'product_id' => $request->product_id,
            'lot_number' => $request->lot_number,
            'lot_expiry' => $request->lot_expiry,
            'qc_date' => $request->qc_date,
            'qc_by' => $request->qc_by,
            'qc_note' => $request->qc_note,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($qcLot, 'QcLot created successfully');
        }

        return redirect()->back()->with('success', 'QcLot created successfully');
    }

    public function show(Request $request, $id)
    {
        $qcLot = QcLot::query()
            ->with('product')
            ->findOrFail($id);

        if ($request->wantsJson()) {
            return $this->sendResponse($qcLot, 'QcLot retrieved successfully');
        }

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $qcLot = QcLot::query()
            ->with('product')
            ->findOrFail($id);
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'lot_number' => 'required|max:200',
            'lot_expiry' => 'nullable|max:200',
            'qc_date' => 'required|date_format:Y-m-d',
            'qc_by' => 'required|max:200',
            'qc_note' => 'nullable|max:200',
        ]);
        $qcLot->update([
            'product_id' => $request->product_id,
            'lot_number' => $request->lot_number,
            'lot_expiry' => $request->lot_expiry,
            'qc_date' => $request->qc_date,
            'qc_by' => $request->qc_by,
            'qc_note' => $request->qc_note,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($qcLot, 'QcLot updated successfully');
        }

        return redirect()->back()->with('success', 'QcLot updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $qcLot = QcLot::query()
            ->with('product')
            ->findOrFail($id);
        $qcLot->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($qcLot, 'QcLot deleted successfully');
        }

        return redirect()->back()->with('success', 'QcLot deleted successfully');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:qc_lots,id',
        ]);
        $deleted = QcLot::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'deleted_count' => $deleted,
            ], 'QcLot deleted successfully.');
        }

        return redirect()->back()->with('success', 'QcLot deleted successfully.');
    }

    public function import(Request $request)
    {
        if ($request->wantsJson() || $request->isMethod('post')) {
            $this->validate($request, [
                'data' => 'required|array',
                'data.*' => 'array',
                'data.*.product' => 'required',
                'data.*.lot' => 'required|max:200',
                'data.*.ed' => 'nullable|max:200',
                'data.*.date' => 'required|date_format:Y-m-d',
                'data.*.qc_by' => 'nullable|max:200',
                'data.*.qc_note' => 'nullable|max:200',
            ]);
            try {
                DB::beginTransaction();
                $qcLots = [];
                foreach ($request->data ?? [] as $key => $item) {
                    $product = Product::query()->where('code', $item['product'])->first();
                    if (! $product) {
                        throw new Exception('Product not found: '.$item['product']);
                    }
                    $qcLots[] = QcLot::create([
                        'product_id' => $product->id,
                        'lot_number' => $item['lot'],
                        'lot_expiry' => $item['ed'],
                        'qc_date' => $item['date'],
                        'qc_by' => $item['qc_by'] ?? null,
                        'qc_note' => $item['qc_note'] ?? null,
                    ]);
                }
                DB::commit();

                if ($request->wantsJson()) {
                    return $this->sendResponse($qcLots, 'QcLot created successfully');
                }

                return redirect()->back()->with('success', 'QcLot created successfully');
            } catch (\Throwable $th) {
                DB::rollBack();

                if ($request->wantsJson()) {
                    return $this->sendError($th->getMessage());
                }

                return redirect()->back()->with('error', $th->getMessage());
            }
        }

        return Inertia::render('QcLot/Import', [
            'title' => 'Import QC Lot',
        ]);
    }
}
