<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bast;
use App\Models\DetailBast;
use App\Models\Product;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BastController extends Controller
{

    public function index()
    {
        $data = Bast::with('details')->latest()->get();
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'      => 'required|max:250',
            'address'   => 'required|max:250',
            'city'      => 'required|max:250',
            'do'        => 'required|max:250',
        ]);
        $bast = Bast::create([
            'name'      => $request->name,
            'address'   => $request->address,
            'city'      => $request->city,
            'do'        => $request->do
        ]);
        return $this->sendResponse($bast, 'Created!');
    }

    public function update(Request $request, Bast $bast)
    {
        $this->validate($request, [
            'name'      => 'required|max:250',
            'address'   => 'required|max:250',
            'city'      => 'required|max:250',
            'do'        => 'required|max:250',
        ]);
        $bast->update([
            'name'      => $request->name,
            'address'   => $request->address,
            'city'      => $request->city,
            'do'        => $request->do,
        ]);
        return $this->sendResponse($bast, 'Updated!');
    }

    public function show(Request $request, Bast $bast)
    {
        return $this->sendResponse($bast->load('details.product'), 'Success!');
    }

    public function destroy(Bast $bast)
    {
        $bast->delete();
        return $this->sendResponse($bast, 'Deleted!');
    }

    public function sync(Bast $bast)
    {
        try {
            $id = 0;
            $do = $bast->do;
            $json = DoServices::getAll($do);
            if (count($json['records'] ?? []) > 0) {
                $id = intval($json['records'][0]['id']);
            }
            $detail = DoServices::detail($id);
            $pd_jd = [];
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

                preg_match('/\[(.*?)\]/', $item['product_id'][1], $matches);
                if (isset($matches[1])) {
                    $pro = Product::query()->where('code', $matches[1])->first();
                    if ($pro) {
                        array_push($pd_jd, [
                            'code'      => $matches[1],
                            'qty'       => $item['quantity_done'],
                            'satuan'    => 'EA',
                            'default'   => $item['product_id'][1],
                            'lot'       => $values,
                        ]);
                        DetailBast::create([
                            'product_id'    => $pro->id,
                            'bast_id'       => $bast->id,
                            'qty'           => $item['quantity_done'],
                            'satuan'        => 'EA',
                            'lot'           => $values,
                        ]);
                    }
                }
            }
            return response()->json(['message' => 'Success!', 'pd_jd' => $pd_jd, 'do' => $do, 'detail' => $detail]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
