<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DetailAlamat;
use App\Models\Product;
use App\Services\DoService;
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
        return response()->json(['message' => '', 'data' => $alamat->load('detail.product')]);
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
            $json4 = $service->method('POST')->data([
                "jsonrpc" => "2.0",
                "method" => "call",
                "params" => [
                    "args" => [
                        $json2['result'][0]['move_line_ids_without_package'] ?? [],
                        [
                            "picking_id",
                            "product_id",
                            "package_level_id",
                            "location_id",
                            "location_dest_id",
                            "lot_id",
                            "expired_date_do",
                            "lot_name",
                            "expired_date",
                            "lot_id2",
                            "package_id",
                            "result_package_id",
                            "owner_id",
                            "is_initial_demand_editable",
                            "product_uom_qty",
                            "qty_availa",
                            "state",
                            "is_locked",
                            "qty_done",
                            "product_uom_id"
                        ]
                    ],
                    "model" => "stock.move.line",
                    "method" => "read",
                    "kwargs" => [
                        "context" => [
                            "lang" => "en_US",
                            "tz" => "Asia/Jakarta",
                            "uid" => 192,
                            "params" => [
                                "action" => 384,
                                "active_id" => 2,
                                "model" => "stock.picking",
                                "view_type" => "list",
                                "menu_id" => 241
                            ],
                            "contact_display" => "partner_address",
                            "search_disable_custom_filters" => true,
                            "active_model" => "stock.move",
                            "active_id" => 2,
                            "active_ids" => [
                                2
                            ],
                            "search_default_picking_type_id" => [
                                2
                            ],
                            "default_picking_type_id" => 2,
                            "search_default_available" => 1,
                            "show_lots_m2o" => true,
                            "show_lots_text" => false,
                            "show_source_location" => "stock.location()",
                            "show_destination_location" => "stock.location()",
                            "show_package" => true,
                            "show_reserved_quantity" => false,
                            "tree_view_ref" => "stock.view_stock_move_line_operation_tree",
                            "default_product_uom_id" => 61,
                            "default_picking_id" => 20018,
                            "default_move_id" => 226881,
                            "default_product_id" => 22006,
                            "default_location_id" => 12,
                            "default_location_dest_id" => 9
                        ]
                    ]
                ],
                "id" => 647906249
            ])->url_param('/web/dataset/call_kw/stock.move/read')->get();

            // return response()->json([
            //     '3' => $json3,
            //     '4' => $json4,
            // ]);
            $x = [];
            foreach ($pd as $item) {
                $lot = collect($json4['result'])->filter(function ($value) use ($item) {
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
                                ->setTimezone('Asia/Jakarta')
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
                        ]);
                    }
                }
            }
            // return response()->json($x);

            return response()->json(['message' => 'Success!', 'pd_jd' => $pd_jd, 'do' => $do, 'json3' => $json3]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
