<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DetailAlamat;
use App\Models\Product;
use App\Services\DoService;
use Illuminate\Http\Request;

class AlamatController extends Controller
{

    public function index()
    {
        return response()->json(['message' => '', 'data' => Alamat::all()]);
    }

    public function show(Alamat $alamat)
    {
        return response()->json(['message' => '', 'data' => $alamat->load('detail.product')]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tujuan'    => 'required',
            'alamat'    => 'required',
            'do'        => 'required',
            'is_do'     => 'nullable|in:yes,no',
            'is_pk'     => 'nullable|in:yes,no',
        ]);

        $param = [
            'tujuan'    => $request->tujuan,
            'alamat'    => $request->alamat,
            'ekspedisi' => $request->ekspedisi,
            'koli'      => $request->koli,
            'up'        => $request->up,
            'tlp'       => $request->tlp,
            'do'        => $request->do,
            'epur'      => $request->epur,
            'untuk'     => $request->untuk,
            'nilai'     => $request->nilai,
            'is_do'     => $request->is_do ?? 'no',
            'is_pk'     => $request->is_pk ?? 'no',
        ];
        $alamat = Alamat::create($param);
        return response()->json(['message' => 'success!', 'data' => $alamat->load('detail')]);
    }

    public function update(Request $request, Alamat $alamat)
    {
        $this->validate($request, [
            'tujuan'    => 'required',
            'alamat'    => 'required',
            'do'        => 'required',
            'is_do'     => 'nullable|in:yes,no',
            'is_pk'     => 'nullable|in:yes,no',
            // 'detail'    => 'required|array|min:1',
        ]);

        $param = [
            'tujuan'    => $request->tujuan,
            'alamat'    => $request->alamat,
            'ekspedisi' => $request->ekspedisi,
            'koli'      => $request->koli,
            'up'        => $request->up,
            'tlp'       => $request->tlp,
            'do'        => $request->do,
            'epur'      => $request->epur,
            'untuk'     => $request->untuk,
            'nilai'     => $request->nilai,
            'is_do'     => $request->is_do ?? 'no',
            'is_pk'     => $request->is_pk ?? 'no',
        ];
        $alamat->update($param);
        return response()->json(['message' => 'success!', 'data' => $alamat->load('detail')]);
    }

    public function destroy(Alamat $alamat)
    {
        $alamat->delete();
        return response()->json(['message' => 'success!', 'data' => $alamat]);
    }

    public function sync(Alamat $alamat)
    {
        try {
            $id = 0;
            $do = $alamat->do;
            $service  = new DoService();
            $json = $service->search($do);
            if (count($json['result']['records'] ?? []) > 0) {
                $id = intval($json['result']['records'][0]['id']);
            }
            $json2 = $service->detail($id);
            $id_prod = $json2['result'][0]['move_ids_without_package'] ?? [];
            $json3 = $service->method('POST')
                ->data([
                    'jsonrpc' => '2.0',
                    'method' => 'call',
                    'params' => [
                        'args' => [
                            $id_prod,
                            [
                                "product_id",
                                "name",
                                "location_id",
                                "x_studio_lot",
                                "x_studio_field_X7gbX",
                                "akl_id",
                                "exp_date",
                                "product_uom_qty",
                                "quantity_done",
                            ]
                        ],
                        'model' => 'stock.move',
                        'method' => 'read',
                        'kwargs' => [
                            'context' => [
                                'lang' => 'en_US',
                                'tz' => 'GMT',
                                'uid' => 192,
                                'active_model' => 'stock.picking.type',
                                'active_id' => 2,
                                'active_ids' => [2],
                                'search_default_picking_type_id' => [2],
                                'default_picking_type_id' => 2,
                                'contact_display' => 'partner_address',
                                'search_default_available' => 1,
                                'search_disable_custom_filters' => true,
                                'picking_type_code' => 'outgoing',
                                'default_picking_id' => 2309,
                                'form_view_ref' => 'stock.view_move_picking_form',
                                'address_in_id' => 25823,
                                'default_location_id' => 12,
                                'default_location_dest_id' => 9
                            ]
                        ]
                    ],
                    'id' => 555446768
                ])
                ->url_param('/web/dataset/call_kw/stock.move/read')
                ->get();
            $pd = $json3['result'] ?? [];
            $pd_jd = [];
            foreach ($pd as $item) {
                preg_match('/\[(.*?)\]/', $item['product_id'][1], $matches);
                if (isset($matches[1])) {
                    $pro = Product::query()->where('code', $matches[1])->first();
                    if ($pro) {
                        array_push($pd_jd, [
                            'code'      => $matches[1],
                            'qty'       => $item['quantity_done'],
                            'default'   => $item['product_id'][1]
                        ]);
                        DetailAlamat::create([
                            'product_id'    => $pro->id,
                            'alamat_id'     => $alamat->id,
                            'qty'           => $item['quantity_done'],
                        ]);
                    }
                }
            }
            return response()->json(['message' => 'Success!', 'pd_jd' => $pd_jd, 'do' => $do, 'json3' => $json3]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
