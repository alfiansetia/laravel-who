<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bast;
use App\Models\DetailBast;
use App\Models\Product;
use App\Models\Setting;
use App\Services\DoService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class BastController extends Controller
{

    public function index()
    {
        $data = Bast::with('details')->latest()->get();
        return response()->json([
            'data' => $data,
            'message' => '',
        ]);
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
        return response()->json([
            'message'   => 'Success!',
            'data'      => $bast
        ]);
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
        return response()->json([
            'message'   => 'Success!',
            'data'      => $bast
        ]);
    }

    public function show(Request $request, Bast $bast)
    {
        return response()->json([
            'message'   => 'Success!',
            'data'      => $bast->load('details.product')
        ]);
    }

    public function destroy(Bast $bast)
    {
        $bast->delete();
        return response()->json([
            'message'   => 'Success!',
            'data'      => $bast
        ]);
    }

    public function sync(Bast $bast)
    {
        try {
            $id = 0;
            $do = $bast->do;
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
                        DetailBast::create([
                            'product_id'    => $pro->id,
                            'bast_id'       => $bast->id,
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
