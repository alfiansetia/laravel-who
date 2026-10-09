<?php

namespace App\Http\Controllers;

use App\Http\Resources\AtkResource;
use App\Models\Atk;
use App\Models\AtkTransaction;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AtkController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = Atk::query()->select('atks.*');

            // computed stok via subquery
            $query->selectSub(function ($sub) {
                $sub->selectRaw("COALESCE(SUM(CASE WHEN type='in' THEN qty ELSE 0 END),0) - COALESCE(SUM(CASE WHEN type='out' THEN qty ELSE 0 END),0)")
                    ->from('atk_transactions')
                    ->whereColumn('atk_transactions.atk_id', 'atks.id');
            }, 'stok');

            // search
            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('code', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%")
                        ->orWhere('satuan', 'like', "%{$keyword}%")
                        ->orWhere('desc', 'like', "%{$keyword}%");
                });
            }

            // satuan filter
            if ($request->filled('satuan')) {
                $query->where('satuan', $request->satuan);
            }

            $total = (clone $query)->count();
            $data = $query->orderBy('code', 'asc')
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

        return Inertia::render('Atk/Index', [
            'title' => 'Data ATK',
            'filters' => $request->only(['search', 'page', 'satuan']),
            'satuanList' => ['pcs', 'dus', 'pack', 'kotak', 'lusin', 'pad', 'rim', 'roll', 'tube', 'box', 'buah', 'buku'],
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:200|unique:atks,code',
            'name' => 'required|max:200',
            'satuan' => 'required|max:200',
            'desc' => 'nullable|max:200',
        ]);
        $atk = Atk::create([
            'code' => $request->code,
            'name' => $request->name,
            'satuan' => $request->satuan,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $atk,
                'message' => 'Success Insert Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Insert Data');
    }

    public function show(Request $request, Atk $atk)
    {
        if ($request->wantsJson()) {
            return response()->json(['data' => new AtkResource($atk->load('transactions'))], 200);
        }

        return redirect()->back();
    }

    public function update(Request $request, Atk $atk)
    {
        $this->validate($request, [
            'code' => 'required|max:200|unique:atks,code,'.$atk->id,
            'name' => 'required|max:200',
            'satuan' => 'required|max:200',
            'desc' => 'nullable|max:200',
        ]);
        $atk->update([
            'code' => $request->code,
            'name' => $request->name,
            'satuan' => $request->satuan,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $atk,
                'message' => 'Success Update Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Update Data');
    }

    public function destroy(Request $request, Atk $atk)
    {
        $atk->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $atk,
                'message' => 'Success Delete Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Delete Data');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:atks,id',
        ]);
        $deleted = Atk::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse(['deleted_count' => $deleted], 'Atk deleted successfully.');
        }

        return redirect()->back()->with('success', 'Atk deleted successfully.');
    }

    public function import(Request $request)
    {
        if ($request->wantsJson() || $request->isMethod('post')) {
            foreach ($request->data ?? [] as $key => $item) {
                Atk::query()->updateOrCreate([
                    'code' => $item['code'],
                ], [
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'satuan' => strtolower($item['satuan']),
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Success',
                ]);
            }

            return redirect()->back()->with('success', 'Success');
        }

        return Inertia::render('Atk/Import', [
            'title' => 'Import Data ATK',
        ]);
    }

    public function trxIndex(Request $request)
    {
        $query = AtkTransaction::with('atk');
        if ($request->filled('atk_id')) {
            $query->where('atk_id', $request->atk_id);
        }
        $data = $query->orderBy('date', 'asc')->get();

        if ($request->wantsJson()) {
            return response()->json(['data' => $data], 200);
        }

        return redirect()->back();
    }

    public function trxStore(Request $request)
    {
        $this->validate($request, [
            'atk_id' => 'required|exists:atks,id',
            'date' => 'required|date_format:Y-m-d',
            'pic' => 'required|max:200',
            'type' => 'required|in:in,out',
            'qty' => 'required|gt:0',
            'desc' => 'nullable|max:200',
        ]);
        $trx = AtkTransaction::create($request->only([
            'atk_id',
            'date',
            'pic',
            'type',
            'qty',
            'desc',
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => 'Success Insert Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Insert Data');
    }

    public function trxShow(Request $request, $id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (! $trx) {
            if ($request->wantsJson()) {
                return response()->json([
                    'data' => null,
                    'message' => 'Data Not Found!',
                ], 404);
            }

            return redirect()->back()->with('error', 'Data Not Found!');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => '',
            ]);
        }

        return redirect()->back();
    }

    public function trxUpdate(Request $request, $id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (! $trx) {
            if ($request->wantsJson()) {
                return response()->json([
                    'data' => null,
                    'message' => 'Data Not Found!',
                ], 404);
            }

            return redirect()->back()->with('error', 'Data Not Found!');
        }
        $this->validate($request, [
            'atk_id' => 'required|exists:atks,id',
            'date' => 'required|date_format:Y-m-d',
            'pic' => 'required|max:200',
            'type' => 'required|in:in,out',
            'qty' => 'required|gt:0',
            'desc' => 'nullable|max:200',
        ]);
        $trx->update($request->only([
            'atk_id',
            'date',
            'pic',
            'type',
            'qty',
            'desc',
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => 'Success Update Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Update Data');
    }

    public function trxDestroy(Request $request, $id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (! $trx) {
            if ($request->wantsJson()) {
                return response()->json([
                    'data' => null,
                    'message' => 'Data Not Found!',
                ], 404);
            }

            return redirect()->back()->with('error', 'Data Not Found!');
        }
        $trx->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => 'Success Delete Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Delete Data');
    }

    public function eksport(Atk $atk)
    {
        $bcms = collect([
            new Breadcrumb('List ATK', route('atk.index'), true),
            new Breadcrumb('Eksport ATK', route('atk.eksport', $atk->id), false),
        ]);
        // $data = Atk::with('transactions')->get();
        $data = $atk->load('transactions');

        return view('atk.export', compact('data', 'bcms'))->with(['title' => 'Import Data ATK']);
    }
}
