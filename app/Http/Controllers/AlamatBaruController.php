<?php

namespace App\Http\Controllers;

use App\Models\AlamatBaru;
use App\Models\Bast;
use App\Models\Koli;
use App\Models\KoliItem;
use App\Models\Product;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AlamatBaruController extends Controller
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

            $query = AlamatBaru::query();

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('do', 'like', "%{$keyword}%")
                        ->orWhere('tujuan', 'like', "%{$keyword}%")
                        ->orWhere('ekspedisi', 'like', "%{$keyword}%")
                        ->orWhere('alamat', 'like', "%{$keyword}%")
                        ->orWhere('up', 'like', "%{$keyword}%");
                });
            }

            $total = (clone $query)->count();

            $data = $query->orderBy('id', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get([
                    'id',
                    'do',
                    'tujuan',
                    'alamat',
                    'ekspedisi',
                    'total_koli',
                    'up',
                    'created_at',
                ]);

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        return Inertia::render('AlamatBaru/Index', [
            'title' => 'List Alamat Baru',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('AlamatBaru/Create', [
            'title' => 'Create Alamat Baru',
        ]);
    }

    public function edit(AlamatBaru $alamatBaru)
    {
        $data = $alamatBaru;
        $products = Product::query()
            ->select('id', 'code', 'name')
            ->orderBy('code')
            ->get();

        return Inertia::render('AlamatBaru/Edit', [
            'title' => 'Edit Alamat Baru',
            'record' => $data,
            'products' => $products,
        ]);
    }

    public function show(Request $request, AlamatBaru $alamatBaru)
    {
        if ($request->wantsJson()) {
            $data = $alamatBaru->load('kolis.items.product');

            return $this->sendResponse($data);
        }

        $is_split = $request->boolean('split') ?? false;
        $data = $alamatBaru;
        $kolis = $alamatBaru->kolis()
            ->when($request->koli_id, function ($q) use ($request) {
                $q->where('id', $request->koli_id);
            })
            ->with('items.product')
            ->get();

        return view('alamat_baru.show', compact('data', 'kolis', 'is_split'))->with(['title' => 'Detail Alamat Baru']);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tujuan' => 'required',
            'alamat' => 'required',
            'do' => 'required',
        ]);
        $totalKoli = $request->total_koli ?? 0;

        $param = [
            'tujuan' => $request->tujuan,
            'alamat' => $request->alamat,
            'ekspedisi' => $request->ekspedisi,
            'total_koli' => $totalKoli,
            'up' => $request->up,
            'tlp' => $request->tlp,
            'do' => $request->do,
            'epur' => $request->epur,
            'untuk' => $request->untuk,
            'note' => $request->note,
            'note_wh' => $request->note_wh,
        ];
        $alamatBaru = AlamatBaru::create($param);
        if ($totalKoli > 0) {
            $urutan = $totalKoli == 1 ? '1' : "1-$totalKoli";
            Koli::create([
                'alamat_baru_id' => $alamatBaru->id,
                'urutan' => $urutan,
                'order' => $totalKoli,
                'is_asuransi' => 'yes',
            ]);
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($alamatBaru->load('kolis'), 'Created!');
        }

        return redirect()->route('alamat_baru.index')->with('success', 'Created!');
    }

    public function update(Request $request, AlamatBaru $alamatBaru)
    {
        $this->validate($request, [
            'tujuan' => 'required',
            'alamat' => 'required',
            'do' => 'required',
        ]);

        $param = [
            'tujuan' => $request->tujuan,
            'alamat' => $request->alamat,
            'ekspedisi' => $request->ekspedisi,
            'total_koli' => $request->total_koli ?? 0,
            'up' => $request->up,
            'tlp' => $request->tlp,
            'do' => $request->do,
            'epur' => $request->epur,
            'untuk' => $request->untuk,
            'note' => $request->note,
            'note_wh' => $request->note_wh,
        ];
        $alamatBaru->update($param);

        if ($request->wantsJson()) {
            return $this->sendResponse($alamatBaru->load('kolis'), 'Updated!');
        }

        return redirect()->route('alamat_baru.index')->with('success', 'Updated!');
    }

    public function destroy(Request $request, AlamatBaru $alamatBaru)
    {
        $alamatBaru->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($alamatBaru, 'Deleted!');
        }

        return redirect()->route('alamat_baru.index')->with('success', 'Deleted!');
    }

    public function duplicate(Request $request, AlamatBaru $alamatBaru)
    {
        $data = $alamatBaru->replicate();
        $data->save();

        foreach ($alamatBaru->kolis as $koli) {
            $newKoli = $koli->replicate();
            $newKoli->alamat_baru_id = $data->id;
            $newKoli->save();

            foreach ($koli->items as $item) {
                $newItem = $item->replicate();
                $newItem->koli_id = $newKoli->id;
                $newItem->save();
            }
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($data, 'Success Duplicate!');
        }

        return redirect()->route('alamat_baru.index')->with('success', 'Success Duplicate!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:alamat_barus,id',
        ]);
        $deleted = AlamatBaru::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'deleted_count' => $deleted,
            ], 'Alamat Baru deleted successfully.');
        }

        return redirect()->route('alamat_baru.index')->with('success', 'Alamat Baru deleted successfully.');
    }

    public function bast(Request $request, AlamatBaru $alamatBaru)
    {
        $no_do = $alamatBaru->do;
        $bast = Bast::query()
            ->where('do', $no_do)
            ->first();
        if ($bast) {
            if ($request->wantsJson()) {
                return $this->sendError('BAST Sudah ada!');
            }

            return redirect()->back()->with('error', 'BAST Sudah ada!');
        }
        $response = DoServices::getAll($no_do, 1);
        $do = Arr::get($response, 'records.0', null);
        if (! $do) {
            if ($request->wantsJson()) {
                return $this->sendError('DO tidak ditemukan!');
            }

            return redirect()->back()->with('error', 'DO tidak ditemukan!');
        }
        $bast = Bast::create([
            'do' => $no_do,
            'address' => Str::replace("\n", ' ', $alamatBaru->alamat),
            'name' => $alamatBaru->tujuan,
            'city' => Arr::get($do, 'partner_address3', ''),
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($bast, 'Success Create BAST!');
        }

        return redirect()->back()->with('success', 'Success Create BAST!');
    }

    public function koliIndex(Request $request)
    {
        $data = Koli::query()
            ->with(['items.product'])
            ->filter($request->only(['alamat_baru_id']))
            ->get();

        return $this->sendResponse($data);
    }

    public function koliStore(Request $request)
    {
        $this->validate($request, [
            'alamat_baru_id' => 'required|exists:alamat_barus,id',
            'urutan' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Cek format range (1-7)
                    if (strpos($value, '-') !== false) {
                        if (! preg_match('/^(\d+)-(\d+)$/', $value, $matches)) {
                            $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');

                            return;
                        }
                        if ((int) $matches[1] >= (int) $matches[2]) {
                            $fail('Untuk range, angka pertama harus lebih kecil dari angka kedua.');
                        }

                        return;
                    }

                    // Cek format angka atau koma (1 atau 1,3,5)
                    if (! preg_match('/^\d+(\s*,\s*\d+)*$/', $value)) {
                        $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');
                    }
                },
            ],
            'nilai' => 'nullable|string',
            'is_do' => 'nullable|in:yes,no',
            'is_pk' => 'nullable|in:yes,no',
            'is_asuransi' => 'nullable|in:yes,no',
            'is_banting' => 'nullable|in:yes,no',
        ]);

        $lastOrder = Koli::where('alamat_baru_id', $request->alamat_baru_id)->max('order') ?? -1;

        $param = [
            'alamat_baru_id' => $request->alamat_baru_id,
            'urutan' => $request->urutan,
            'nilai' => $request->nilai,
            'is_do' => $request->is_do ?? 'no',
            'is_pk' => $request->is_pk ?? 'no',
            'is_asuransi' => $request->is_asuransi ?? 'no',
            'is_banting' => $request->is_banting ?? 'no',
            'order' => $lastOrder + 1,
        ];

        $koli = Koli::create($param);

        if ($request->wantsJson()) {
            return $this->sendResponse($koli->load('items'), 'Koli created!');
        }

        return redirect()->back()->with('success', 'Koli created!');
    }

    public function koliUpdate(Request $request, Koli $koli)
    {
        $this->validate($request, [
            'urutan' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Cek format range (1-7)
                    if (strpos($value, '-') !== false) {
                        if (! preg_match('/^(\d+)-(\d+)$/', $value, $matches)) {
                            $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');

                            return;
                        }
                        if ((int) $matches[1] >= (int) $matches[2]) {
                            $fail('Untuk range, angka pertama harus lebih kecil dari angka kedua.');
                        }

                        return;
                    }

                    // Cek format angka atau koma (1 atau 1,3,5)
                    if (! preg_match('/^\d+(\s*,\s*\d+)*$/', $value)) {
                        $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');
                    }
                },
            ],
            'nilai' => 'nullable|string',
            'is_do' => 'nullable|in:yes,no',
            'is_pk' => 'nullable|in:yes,no',
            'is_asuransi' => 'nullable|in:yes,no',
            'is_banting' => 'nullable|in:yes,no',
        ]);

        $param = [
            'urutan' => $request->urutan,
            'nilai' => $request->nilai,
            'is_do' => $request->is_do ?? 'no',
            'is_pk' => $request->is_pk ?? 'no',
            'is_asuransi' => $request->is_asuransi ?? 'no',
            'is_banting' => $request->is_banting ?? 'no',
        ];

        $koli->update($param);

        if ($request->wantsJson()) {
            return $this->sendResponse($koli->load('items'), 'Koli updated!');
        }

        return redirect()->back()->with('success', 'Koli updated!');
    }

    public function koliShow(Request $request, Koli $koli)
    {
        $koli->load(['items.product', 'alamatBaru']);

        if ($request->wantsJson()) {
            return $this->sendResponse($koli, '');
        }

        return redirect()->back();
    }

    public function koliDestroy(Request $request, Koli $koli)
    {
        $koli->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($koli, 'Koli deleted!');
        }

        return redirect()->back()->with('success', 'Koli deleted!');
    }

    public function koliSync(Request $request, Koli $koli)
    {
        $id = 0;
        $do = $koli->alamatBaru->do;
        $json = DoServices::getAll($do);
        if (count($json['records'] ?? []) > 0) {
            $id = intval($json['records'][0]['id']);
        }
        $detail = DoServices::detail($id);
        $pd_jd = [];

        $last_key = 0;
        $details = KoliItem::query()
            ->where('koli_id', $koli->id)
            ->orderBy('order')
            ->get();
        foreach ($details as $key => $item) {
            $item->update([
                'order' => $key,
            ]);
            $last_key++;
        }
        foreach (($detail['move_ids_detail'] ?? []) as $item) {
            $lot = collect(($detail['move_line_detail'] ?? []))->filter(function ($value) use ($item) {
                if (isset($item['product_id'][0], $value['product_id'][0])) {
                    return $item['product_id'][0] === $value['product_id'][0];
                }
            });

            if ($lot->count() <= 2) {
                $values = $lot->map(function ($item) {
                    $lot = $item['lot_id'][1] ?? '';
                    $ed = $item['expired_date_do'] ?? '';
                    if ($lot && $ed) {
                        $ed = odoo_datetime($ed, 'd/m/Y');

                        return $lot.' Ed. '.$ed;
                    } elseif ($lot) {
                        return $lot;
                    }
                })->implode(', ');
            } else {
                $values = '';
            }

            preg_match('/\[(.*?)\]/', ($item['product_id'][1] ?? ''), $matches);
            if (isset($matches[1])) {
                $pro = Product::query()->where('code', $matches[1])->first();
                if ($pro) {
                    array_push($pd_jd, [
                        'code' => $matches[1],
                        'qty' => $item['quantity_done'].' Ea',
                        'default' => $item['product_id'][1],
                        'lot' => $values,
                    ]);
                    KoliItem::create([
                        'koli_id' => $koli->id,
                        'product_id' => $pro->id,
                        'qty' => $item['quantity_done'].' Ea',
                        'lot' => $values,
                        'order' => $last_key,
                    ]);
                    $last_key++;
                }
            }
        }

        if ($request->wantsJson()) {
            return $this->sendResponse(['message' => 'Success!', 'pd_jd' => $pd_jd, 'do' => $do, 'detail' => $detail]);
        }

        return redirect()->back()->with('success', 'Success!');
    }

    public function koliDuplicate(Request $request, Koli $koli)
    {
        $koli->load(['items', 'alamatBaru']);
        $newKoli = $koli->replicate();
        $newKoli->urutan = ($koli->alamatBaru->total_koli ?? 0) + 1;
        $newKoli->save();
        foreach ($koli->items as $item) {
            $newItem = $item->replicate();
            $newItem->koli_id = $newKoli->id;
            $newItem->save();
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($newKoli->load('items'), 'Koli duplicated!');
        }

        return redirect()->back()->with('success', 'Koli duplicated!');
    }

    public function koliHitung(Request $request, Koli $koli)
    {
        $koli->load(['items.product', 'alamatBaru']);

        // Skip if koli has no items
        if ($koli->items->isEmpty()) {
            if ($request->wantsJson()) {
                return $this->sendError('Koli ini belum memiliki data barang!', 400);
            }

            return redirect()->back()->with('error', 'Koli ini belum memiliki data barang!');
        }

        // 1. Get DO number from alamat_baru
        $doNumber = $koli->alamatBaru->do ?? null;
        if (empty($doNumber)) {
            if ($request->wantsJson()) {
                return $this->sendError('Nomor DO tidak ditemukan!', 404);
            }

            return redirect()->back()->with('error', 'Nomor DO tidak ditemukan!');
        }

        // 2. Fetch DO from Odoo
        try {
            $doRecords = DoServices::getAll($doNumber);
        } catch (\Throwable $th) {
            if ($request->wantsJson()) {
                return $this->sendError('Gagal mengambil data DO dari Odoo!', 500);
            }

            return redirect()->back()->with('error', 'Gagal mengambil data DO dari Odoo!');
        }

        $doId = 0;
        if (count($doRecords['records'] ?? []) > 0) {
            $doId = intval($doRecords['records'][0]['id']);
        }
        if ($doId === 0) {
            if ($request->wantsJson()) {
                return $this->sendError('Data DO tidak ditemukan di Odoo!', 404);
            }

            return redirect()->back()->with('error', 'Data DO tidak ditemukan di Odoo!');
        }

        // 3. Get DO detail (includes so_detail.order_line_detail)
        try {
            $doDetail = DoServices::detail($doId);
        } catch (\Throwable $th) {
            if ($request->wantsJson()) {
                return $this->sendError('Gagal mengambil detail DO dari Odoo!', 500);
            }

            return redirect()->back()->with('error', 'Gagal mengambil detail DO dari Odoo!');
        }

        $soLines = $doDetail['so_detail']['order_line_detail'] ?? [];

        // 4. Build lookup map: default_code => order_line
        $soLineMap = [];
        foreach ($soLines as $line) {
            $code = $line['default_code'] ?? null;
            if ($code) {
                $soLineMap[$code] = $line;
            }
        }

        // 5. Calculate value for each koli item
        $totalNilai = 0;
        foreach ($koli->items as $item) {
            $productCode = $item->product->code ?? null;
            if (! $productCode || ! isset($soLineMap[$productCode])) {
                continue; // Skip if not found in SO
            }

            $soLine = $soLineMap[$productCode];
            $priceSubtotal = floatval($soLine['price_subtotal'] ?? 0);
            $productUomQty = floatval($soLine['product_uom_qty'] ?? 0);

            if ($productUomQty <= 0) {
                continue; // Avoid division by zero
            }

            // Parse koli qty from string like "3 Ea" -> 3
            $koliQty = intval(explode(' ', $item->qty ?? '0')[0]);

            $itemValue = ($priceSubtotal / $productUomQty) * $koliQty * 1.11;
            $totalNilai += $itemValue;
        }

        $nilai = (int) round($totalNilai);
        $koli->update(['nilai' => $nilai]);

        if ($request->wantsJson()) {
            return $this->sendResponse($koli->load('items'), 'Koli berhasil dihitung!');
        }

        return redirect()->back()->with('success', 'Koli berhasil dihitung!');
    }

    public function koliItemIndex(Request $request)
    {
        $query = KoliItem::query()->with('product');

        if ($request->filled('koli_id')) {
            $query->where('koli_id', $request->koli_id);
        }

        $data = $query->orderBy('order')->orderBy('id')->get();

        if ($request->wantsJson()) {
            return $this->sendResponse($data, '');
        }

        return redirect()->back();
    }

    public function koliItemStore(Request $request)
    {
        $this->validate($request, [
            'koli_id' => 'required|exists:kolis,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'nullable|string',
            'lot' => 'nullable|string',
            'desc' => 'nullable|string',
        ]);

        $lastOrder = KoliItem::where('koli_id', $request->koli_id)->max('order') ?? -1;

        $param = [
            'koli_id' => $request->koli_id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'lot' => $request->lot,
            'desc' => $request->desc,
            'order' => $lastOrder + 1,
        ];

        $item = KoliItem::create($param);

        if ($request->wantsJson()) {
            return $this->sendResponse($item->load('product'), 'Item created!');
        }

        return redirect()->back()->with('success', 'Item created!');
    }

    public function koliItemUpdate(Request $request, KoliItem $koliItem)
    {
        $this->validate($request, [
            'qty' => 'nullable|string',
            'lot' => 'nullable|string',
            'desc' => 'nullable|string',
        ]);

        $param = [
            'qty' => $request->qty,
            'lot' => $request->lot,
            'desc' => $request->desc,
        ];

        $koliItem->update($param);

        if ($request->wantsJson()) {
            return $this->sendResponse($koliItem->load('product'), 'Item updated!');
        }

        return redirect()->back()->with('success', 'Item updated!');
    }

    public function koliItemShow(Request $request, KoliItem $koliItem)
    {
        $koliItem->load(['product', 'koli']);

        if ($request->wantsJson()) {
            return $this->sendResponse($koliItem, '');
        }

        return redirect()->back();
    }

    public function koliItemDestroy(Request $request, KoliItem $koliItem)
    {
        $koliItem->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($koliItem, 'Item deleted!');
        }

        return redirect()->back()->with('success', 'Item deleted!');
    }

    public function koliItemOrder(Request $request, KoliItem $koliItem)
    {
        $this->validate($request, [
            'type' => 'required|in:up,down',
        ]);

        $currentOrder = $koliItem->order;
        $koliId = $koliItem->koli_id;

        if ($request->type === 'up') {
            $swapItem = KoliItem::where('koli_id', $koliId)
                ->where('order', '<', $currentOrder)
                ->orderBy('order', 'desc')
                ->first();

            if ($swapItem) {
                $tempOrder = $swapItem->order;
                $swapItem->update(['order' => $currentOrder]);
                $koliItem->update(['order' => $tempOrder]);
            }
        } else {
            $swapItem = KoliItem::where('koli_id', $koliId)
                ->where('order', '>', $currentOrder)
                ->orderBy('order', 'asc')
                ->first();

            if ($swapItem) {
                $tempOrder = $swapItem->order;
                $swapItem->update(['order' => $currentOrder]);
                $koliItem->update(['order' => $tempOrder]);
            }
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($koliItem, 'Order updated!');
        }

        return redirect()->back()->with('success', 'Order updated!');
    }

    public function koliItemClearLot(Request $request, KoliItem $koliItem)
    {
        $koliItem->update(['lot' => null]);

        if ($request->wantsJson()) {
            return $this->sendResponse($koliItem, 'Lot cleared!');
        }

        return redirect()->back()->with('success', 'Lot cleared!');
    }

    public function koliItemFromDoIt(Request $request)
    {
        $this->validate($request, [
            'koli_id' => 'required|exists:kolis,id',
            'product_code' => 'required|exists:products,code',
            'lot' => 'nullable|string',
            'qty' => 'required|string',
        ]);

        $prod = Product::where('code', $request->product_code)->firstOrFail();
        $lastOrder = KoliItem::where('koli_id', $request->koli_id)->max('order') ?? -1;

        $item = KoliItem::create([
            'koli_id' => $request->koli_id,
            'product_id' => $prod->id,
            'lot' => $request->lot,
            'qty' => $request->qty,
            'order' => $lastOrder + 1,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($item, 'Item Added!');
        }

        return redirect()->back()->with('success', 'Item Added!');
    }
}
