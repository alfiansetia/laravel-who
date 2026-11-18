<?php

namespace App\Http\Controllers;

use App\Http\Resources\PoResource;
use App\Services\Import\CekImportService;
use App\Services\Import\POServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class POController extends Controller
{
    public function index(Request $request, POServices $po)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');
            $product_search = (string) ($request->input('product_search') ?? '');

            $response = $po->getAll($search, $perPage, $offset, $product_search);
            $total = Arr::get($response, 'length', 0);
            $data = Arr::get($response, 'records', []);

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
            ]);
        }

        return Inertia::render('Po/Index', [
            'title' => 'PO',
            'filters' => $request->only(['search', 'page', 'product_search']),
        ]);
    }

    public function detail(Request $request, string $id, POServices $po)
    {
        $res = $po->detail((int) $id);

        return $this->sendResponse($res);
    }

    public function order_line(Request $request, POServices $po)
    {
        $res = $po->getOrderLines((array) $request->lines);

        if ($request->wantsJson()) {
            return $this->sendResponse(['data' => PoResource::collection($res)]);
        }

        return redirect()->back()->with('success', 'Success!');
    }

    public function cekImport(string $id, POServices $poServices)
    {
        $po = $poServices->detail((int) $id);

        $poName = (string) ($po['name'] ?? '');
        // Dari PO, RI belum pasti (satu PO bisa punya banyak RI) → pakai dummy.
        $riName = 'CENT/IN/';

        $partner = $po['partner_detail'] ?? null;
        $vendorName = CekImportService::vendorName(is_array($partner) ? $partner : null, $po['partner_id'] ?? null);
        $vendorAddress = CekImportService::composeAddress(is_array($partner) ? $partner : null);

        $items = collect($po['order_line_detail'] ?? [])->map(function ($line) {
            [$code, $name] = CekImportService::splitProduct($line['product_id'] ?? null);

            return [
                'code' => $code,
                'name' => $name !== '' ? $name : (string) ($line['name'] ?? ''),
                'qty' => $line['product_qty'] ?? 0,
            ];
        })->all();

        $path = CekImportService::build($poName, $riName, $vendorName, $vendorAddress, $items);

        return response()->download($path, CekImportService::filename($poName, $vendorName), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
