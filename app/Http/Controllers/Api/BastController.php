<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bast;
use App\Models\DetailBast;
use App\Models\Product;
use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class BastController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $setting = Setting::first();
        $this->headers['Cookie'] = 'session_id=' . $setting->odoo_session ?? '';
    }

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
        $odoo_domain = env('ODOO_DOMAIN');
        try {
            $do = $bast->do;
            $url = $odoo_domain . '/web/dataset/search_read';
            $param = [
                'jsonrpc' => '2.0',
                'method' => 'call',
                'params' => [
                    'model' => 'stock.picking',
                    'domain' => [
                        [
                            'picking_type_id',
                            '=',
                            2
                        ],
                        '|',
                        [
                            'name',
                            'ilike',
                            $do
                        ],
                        [
                            'origin',
                            'ilike',
                            $do
                        ]
                    ],
                    'fields' => [
                        'name',
                    ],
                    'limit' => 1,
                    'sort' => '',
                    'context' => [
                        'lang' => 'en_US',
                        'tz' => 'GMT',
                        'uid' => 192,
                        'active_model' => 'stock.picking.type',
                        'active_id' => 2,
                        'active_ids' => [
                            2
                        ],
                        'search_default_picking_type_id' => [
                            2
                        ],
                        'default_picking_type_id' => 2,
                        'contact_display' => 'partner_address',
                        'search_default_available' => 1,
                        'search_disable_custom_filters' => true
                    ]
                ],
                'id' => 654266576
            ];
            $json = $this->handle_res($url, $param);
            if (count($json['result']['records'] ?? []) > 0) {
                $id = intval($json['result']['records'][0]['id']);
            }
            $url2 = $odoo_domain . '/web/dataset/call_kw/stock.picking/read';
            $param2 = [
                'jsonrpc'   => '2.0',
                'method'    => 'call',
                'params'    => [
                    'args'  => [
                        [$id],
                        [
                            "id",
                            "cancel_done_picking",
                            "picking_type_code",
                            "show_operations",
                            "move_line_exist",
                            "state",
                            "picking_type_entire_packs",
                            "has_scrap_move",
                            "has_tracking",
                            "name",
                            "x_studio_field_2kd16",
                            "x_studio_no_do_manual",
                            "partner_id",
                            "partner_address",
                            "partner_address2",
                            "partner_address3",
                            "partner_address4",
                            "x_studio_customer",
                            "bill_to",
                            "delivery_manual",
                            "ekspedisi_id",
                            "x_studio_update_eta",
                            "pp",
                            "ppk",
                            "location_id",
                            "kurir_id",
                            "kurir_state",
                            "tanggal_sampai",
                            "tanggal_persetujuan",
                            "location_dest_id",
                            "picking_type_id",
                            "backorder_id",
                            "date",
                            "force_date",
                            "x_studio_date",
                            "date_done",
                            "confirmation_date_so",
                            "print_x_studio_date",
                            "print_tanggal_sampai",
                            "tgl_bast",
                            "origin",
                            "x_studio_id_paket",
                            "x_studio_no_po",
                            "x_studio_no_ska",
                            "itr_id",
                            "invoice_state",
                            "cancel_reason",
                            "note_itr",
                            "receipts_date",
                            "no_po",
                            "no_aks",
                            "no_ska",
                            "no_sph",
                            "no_si",
                            "tgl_akhir_kontrak",
                            "note_to_wh",
                            "priority_so",
                            "owner_id",
                            "penanggung_jawab",
                            "jumlah_hari",
                            "partner",
                            "schedule_id",
                            "move_line_ids_without_package",
                            "package_level_ids_details",
                            "immediate_transfer",
                            "move_ids_without_package",
                            "package_level_ids",
                            "move_type",
                            "sale_id",
                            "company_id",
                            "group_id",
                            "scheduled_date",
                            "priority",
                            "note",
                            "move_temp_id",
                            "message_follower_ids",
                            "activity_ids",
                            "display_name"
                        ]
                    ],
                    'model' => 'stock.picking',
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
                            'bin_size' => true
                        ]
                    ]
                ],
                'id' => 334664946
            ];
            $json2 = $this->handle_res($url2, $param2);
            $id_prod = $json2['result'][0]['move_ids_without_package'] ?? [];

            $url3 = $odoo_domain . '/web/dataset/call_kw/stock.move/read';
            $param3 = [
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
            ];
            $json3 = $this->handle_res($url3, $param3);
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
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
