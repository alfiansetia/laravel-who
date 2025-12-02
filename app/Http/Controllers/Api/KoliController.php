<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Koli;
use App\Models\KoliItem;
use App\Models\Product;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KoliController extends Controller
{

    public function index(Request $request)
    {
        $data = Koli::query()
            ->with(['items.product'])
            ->filter($request->only(['alamat_baru_id']))
            ->latest()
            ->get();
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'alamat_baru_id'    => 'required|exists:alamat_barus,id',
            'urutan'            => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Cek format range (1-7)
                    if (strpos($value, '-') !== false) {
                        if (!preg_match('/^(\d+)-(\d+)$/', $value, $matches)) {
                            $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');
                            return;
                        }
                        if ((int)$matches[1] >= (int)$matches[2]) {
                            $fail('Untuk range, angka pertama harus lebih kecil dari angka kedua.');
                        }
                        return;
                    }

                    // Cek format angka atau koma (1 atau 1,3,5)
                    if (!preg_match('/^\d+(\s*,\s*\d+)*$/', $value)) {
                        $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');
                    }
                },
            ],
            'nilai'             => 'nullable|string',
            'is_do'             => 'nullable|in:yes,no',
            'is_pk'             => 'nullable|in:yes,no',
            'is_asuransi'       => 'nullable|in:yes,no',
            'is_banting'        => 'nullable|in:yes,no',
        ]);

        $lastOrder = Koli::where('alamat_baru_id', $request->alamat_baru_id)->max('order') ?? -1;

        $param = [
            'alamat_baru_id'    => $request->alamat_baru_id,
            'urutan'            => $request->urutan,
            'nilai'             => $request->nilai,
            'is_do'             => $request->is_do ?? 'no',
            'is_pk'             => $request->is_pk ?? 'no',
            'is_asuransi'       => $request->is_asuransi ?? 'no',
            'is_banting'        => $request->is_banting ?? 'no',
            'order'             => $lastOrder + 1,
        ];

        $koli = Koli::create($param);
        return $this->sendResponse($koli->load('items'), 'Koli created!');
    }

    public function update(Request $request, Koli $koli)
    {
        $this->validate($request, [
            'urutan'            => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Cek format range (1-7)
                    if (strpos($value, '-') !== false) {
                        if (!preg_match('/^(\d+)-(\d+)$/', $value, $matches)) {
                            $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');
                            return;
                        }
                        if ((int)$matches[1] >= (int)$matches[2]) {
                            $fail('Untuk range, angka pertama harus lebih kecil dari angka kedua.');
                        }
                        return;
                    }

                    // Cek format angka atau koma (1 atau 1,3,5)
                    if (!preg_match('/^\d+(\s*,\s*\d+)*$/', $value)) {
                        $fail('Format urutan salah. Gunakan format angka (1), range (1-7), atau koma (1,3,5).');
                    }
                },
            ],
            'nilai'         => 'nullable|string',
            'is_do'         => 'nullable|in:yes,no',
            'is_pk'         => 'nullable|in:yes,no',
            'is_asuransi'   => 'nullable|in:yes,no',
            'is_banting'    => 'nullable|in:yes,no',
        ]);

        $param = [
            'urutan'        => $request->urutan,
            'nilai'         => $request->nilai,
            'is_do'         => $request->is_do ?? 'no',
            'is_pk'         => $request->is_pk ?? 'no',
            'is_asuransi'   => $request->is_asuransi ?? 'no',
            'is_banting'    => $request->is_banting ?? 'no',
        ];

        $koli->update($param);
        return $this->sendResponse($koli->load('items'), 'Koli updated!');
    }

    public function show(Koli $koli)
    {
        $koli->load(['items.product', 'alamatBaru']);
        return $this->sendResponse($koli, '');
    }

    public function destroy(Koli $koli)
    {
        $koli->delete();
        return $this->sendResponse($koli, 'Koli deleted!');
    }

    public function sync(Koli $koli)
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
                        $ed = Carbon::createFromFormat('Y-m-d H:i:s', $ed, 'UTC')
                            ->setTimezone(config('app.timezone'))
                            ->format('d/m/Y');
                        return $lot . " Ed. " . $ed;
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
                        'code'      => $matches[1],
                        'qty'       => $item['quantity_done'] . ' Ea',
                        'default'   => $item['product_id'][1],
                        'lot'       => $values,
                    ]);
                    KoliItem::create([
                        'koli_id'       => $koli->id,
                        'product_id'    => $pro->id,
                        'qty'           => $item['quantity_done'] . ' Ea',
                        'lot'           => $values,
                        'order'         => $last_key
                    ]);
                    $last_key++;
                }
            }
        }
        return $this->sendResponse(['message' => 'Success!', 'pd_jd' => $pd_jd, 'do' => $do, 'detail' => $detail]);
    }

    public function duplicate(Request $request, Koli $koli)
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
        return $this->sendResponse($newKoli->load('items'), 'Koli duplicated!');
    }
}
