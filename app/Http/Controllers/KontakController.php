<?php

namespace App\Http\Controllers;

use App\Models\Kontak;
use App\Services\Odoo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KontakController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = Kontak::query();

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('street', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            }

            $total = (clone $query)->count();

            $data = $query->orderBy('name', 'asc')
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

        return Inertia::render('Kontak/Index', [
            'title' => 'Kontak',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function store(Request $request)
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => 'res.partner',
                'domain' => [],
                'fields' => [
                    'nomor_partner',
                    'kode',
                    'display_name',
                    'nama_faktur',
                    'parent_name',
                    'street',
                    'kota_id',
                    'function',
                    'phone',
                    'user_id',
                    'is_company',
                    'country_id',
                    'parent_id',
                    'active',
                ],
                'limit' => 10000,
                'sort' => '',
                'context' => [
                    'lang' => 'en_US',
                    'tz' => 'Asia/Jakarta',
                    'uid' => 192,
                ],
            ],
            ['id' => 288682884],
        ];
        $url_param = '/web/dataset/search_read';
        $json = Odoo::asJson()
            ->withUrlParam($url_param)
            ->withData($data)
            ->method('POST')
            ->get();
        $records = $json['result']['records'] ?? [];
        $chunks = array_chunk($records, 100);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $item) {
                Kontak::query()->updateOrCreate([
                    'name' => $item['display_name'],
                ], [
                    'name' => $item['display_name'],
                    'street' => $item['street'] ?? '',
                    'phone' => $item['phone'],
                ]);
            }
        }

        if ($request->wantsJson()) {
            return $this->sendResponse(['message' => 'Success!', 'data' => $json['result']]);
        }

        return redirect()->back()->with('success', 'Success!');
    }
}
