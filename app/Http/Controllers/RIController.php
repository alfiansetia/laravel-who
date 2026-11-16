<?php

namespace App\Http\Controllers;

use App\Services\Import\CekImportService;
use App\Services\Import\POServices;
use App\Services\Import\RIServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class RIController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');
            $product_search = (string) ($request->input('product_search') ?? '');

            $response = RIServices::getAll($search, $perPage, $offset, $product_search);
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

        return Inertia::render('Ri/Index', [
            'title' => 'RI',
            'filters' => $request->only(['search', 'page', 'product_search']),
        ]);
    }

    public function detail(Request $request, string $id)
    {
        $res = RIServices::detail($id);

        return $this->sendResponse($res);
    }

    public function order_line(Request $request)
    {
        $res = RIServices::getOrderLines($request->lines);

        if ($request->wantsJson()) {
            return $this->sendResponse(['data' => collect($res['result'])->map(function ($item) {
                $p = pecah_code($item['product_id']);
                $item['p_id'] = $p[0];
                $item['p_code'] = $p[1];
                $item['p_name'] = $p[2];
                $item['akl'] = get_name($item['akl_id']);

                return $item;
            })]);
        }

        return redirect()->back()->with('success', 'Success!');
    }

    public function cekImport(string $id)
    {
        $ri = RIServices::detail((int) $id);

        $riName = (string) ($ri['name'] ?? '');
        $poName = (string) ($ri['origin'] ?? '');

        // Item + vendor diutamakan dari PO (origin) agar qty = qty PO.
        $po = $this->findPo($poName);
        if ($po) {
            $poName = (string) ($po['name'] ?? $poName);
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
        } else {
            $vendorName = CekImportService::vendorName(null, $ri['partner_id'] ?? null);
            $vendorAddress = $this->riAddress($ri);
            $items = collect($ri['move_without_package_detail'] ?? [])->map(function ($line) {
                [$code, $name] = CekImportService::splitProduct($line['product_id'] ?? null);

                return [
                    'code' => $code,
                    'name' => $name !== '' ? $name : (string) ($line['name'] ?? ''),
                    'qty' => $line['product_uom_qty'] ?? 0,
                ];
            })->all();
        }

        $path = CekImportService::build($poName, $riName, $vendorName, $vendorAddress, $items);

        return response()->download($path, CekImportService::filename($poName, $vendorName), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function findPo(string $origin): ?array
    {
        $origin = trim($origin);
        if ($origin === '') {
            return null;
        }
        try {
            $list = POServices::getAll($origin, 5, 0);
            $records = $list['records'] ?? [];
            $match = collect($records)->firstWhere('name', $origin) ?? ($records[0] ?? null);
            if (! $match) {
                return null;
            }

            return POServices::detail((int) $match['id']);
        } catch (\Throwable) {
            return null;
        }
    }

    private function riAddress(array $ri): string
    {
        $parts = [];
        foreach (['partner_address', 'partner_address2', 'partner_address3', 'partner_address4'] as $key) {
            $v = $ri[$key] ?? null;
            $v = is_array($v) ? ($v[1] ?? '') : $v;
            $v = trim((string) $v);
            if ($v !== '' && strtolower($v) !== 'false') {
                $parts[] = $v;
            }
        }

        return $parts === [] ? '-' : implode(', ', $parts);
    }
}
