<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DetailAlamat;
use App\Models\Product;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AlamatController extends Controller
{

    public function index()
    {
        return response()->json(['message' => '', 'data' => Alamat::latest()->get()]);
    }

    public function show(Alamat $alamat)
    {
        $detail = $alamat->detail()->get();
        foreach ($detail as $key => $item) {
            $item->update(['order' => $key]);
        }
        $data = $alamat->load('detail.product');
        return response()->json(['message' => '', 'data' => $data]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tujuan'        => 'required',
            'alamat'        => 'required',
            'do'            => 'required',
            'is_do'         => 'nullable|in:yes,no',
            'is_pk'         => 'nullable|in:yes,no',
            'is_banting'    => 'nullable|in:yes,no',
            'is_last_koli'  => 'nullable|in:yes,no',
        ]);

        $param = [
            'tujuan'        => $request->tujuan,
            'alamat'        => $request->alamat,
            'ekspedisi'     => $request->ekspedisi,
            'koli'          => $request->koli,
            'up'            => $request->up,
            'tlp'           => $request->tlp,
            'do'            => $request->do,
            'epur'          => $request->epur,
            'untuk'         => $request->untuk,
            'nilai'         => $request->nilai,
            'note'          => $request->note,
            'is_do'         => $request->is_do ?? 'no',
            'is_pk'         => $request->is_pk ?? 'no',
            'is_banting'    => $request->is_banting ?? 'no',
            'is_last_koli'  => $request->is_last_koli ?? 'no',
        ];
        $alamat = Alamat::create($param);
        return response()->json(['message' => 'success!', 'data' => $alamat->load('detail')]);
    }

    public function update(Request $request, Alamat $alamat)
    {
        $this->validate($request, [
            'tujuan'        => 'required',
            'alamat'        => 'required',
            'do'            => 'required',
            'is_do'         => 'nullable|in:yes,no',
            'is_pk'         => 'nullable|in:yes,no',
            'is_banting'    => 'nullable|in:yes,no',
            'is_last_koli'  => 'nullable|in:yes,no',
            // 'detail'    => 'required|array|min:1',
        ]);

        $param = [
            'tujuan'        => $request->tujuan,
            'alamat'        => $request->alamat,
            'ekspedisi'     => $request->ekspedisi,
            'koli'          => $request->koli,
            'up'            => $request->up,
            'tlp'           => $request->tlp,
            'do'            => $request->do,
            'epur'          => $request->epur,
            'untuk'         => $request->untuk,
            'nilai'         => $request->nilai,
            'note'          => $request->note,
            'is_do'         => $request->is_do ?? 'no',
            'is_pk'         => $request->is_pk ?? 'no',
            'is_banting'    => $request->is_banting ?? 'no',
            'is_last_koli'  => $request->is_last_koli ?? 'no',
        ];
        $alamat->update($param);
        return response()->json(['message' => 'success!', 'data' => $alamat->load('detail')]);
    }

    public function destroy(Alamat $alamat)
    {
        $alamat->delete();
        return response()->json(['message' => 'success!', 'data' => $alamat]);
    }

    public function duplicate(Alamat $alamat)
    {
        $data = $alamat->replicate();
        $data->save();
        foreach ($alamat->detail as $item) {
            $newItem = $item->replicate();
            $newItem->alamat_id = $data->id;
            $newItem->save();
        }

        return response()->json(['message' => 'success!', 'data' => $data]);
    }

    public function sync(Alamat $alamat)
    {
        try {
            $id = 0;
            $do = $alamat->do;
            $json = DoServices::getAll($do);
            if (count($json['records'] ?? []) > 0) {
                $id = intval($json['records'][0]['id']);
            }
            $detail = DoServices::detail($id);
            $pd_jd = [];

            $last_key = 0;
            $details = DetailAlamat::query()->where('alamat_id', $alamat->id)->orderBy('order')->get();
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
                                ->format('Y.m.d');
                            return $lot . " /Ed. " . $ed;
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
                        DetailAlamat::create([
                            'product_id'    => $pro->id,
                            'alamat_id'     => $alamat->id,
                            'qty'           => $item['quantity_done'] . ' Ea',
                            'lot'           => $values,
                            'order'         => $last_key
                        ]);
                        $last_key++;
                    }
                }
            }
            return response()->json(['message' => 'Success!', 'pd_jd' => $pd_jd, 'do' => $do, 'detail' => $detail]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
