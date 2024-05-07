<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\DetailAlamat;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AlamatController extends Controller
{
    public function create(Request $request)
    {
        $data = Alamat::first() ?? Alamat::factory()->create();
        $products = Product::all();
        return view('alamat.create', compact('data', 'products'))->with(['title' => 'Alamat']);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tujuan'    => 'required',
            'alamat'    => 'required',
            'do'        => 'required',
            'detail'    => 'required|array|min:1',
        ]);

        $alamat = Alamat::first();
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
        if (!$alamat) {
            $alamat = Alamat::create($param);
        } else {
            $alamat->update($param);
            foreach ($alamat->detail as $value) {
                $value->delete();
            }
        }

        foreach ($request->detail as $value) {
            DetailAlamat::create([
                'product_id'    => $value['id'],
                'alamat_id'     => $alamat->id,
                'qty'           => $value['qty'],
                'lot'           => $value['lot'],
            ]);
        }
        return response()->json($alamat->load('detail'));
    }

    public function show(Alamat $alamat)
    {
        $data = $alamat;
        return view('alamat.show', compact('data'))->with(['title' => 'Detail Alamat']);
    }

    public function get(Request $request)
    {
        $ses_id = env('ODOO_SESSION_ID');
        $url = 'http://map.integrasi.online:8069/web/dataset/search_read';

        $headers = [
            'accept' => 'application/json, text/javascript, */*; q=0.01',
            'accept-language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'content-type' => 'application/json',
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $ses_id
        ];

        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => 'product.template',
                'domain' => [
                    [
                        'type',
                        'in',
                        [
                            'consu',
                            'product'
                        ]
                    ]
                ],
                'fields' => [
                    'sequence',
                    'default_code',
                    'name',
                    'categ_id',
                    'akl_id',
                    'x_studio_valid_to_akl',
                    'type',
                    'qty_available',
                    'virtual_available',
                    'uom_id',
                    'active',
                    'x_studio_field_i3XMM',
                    'description'
                ],
                'limit' => 4000,
                'sort' => ''
            ],
            'id' => 353031512
        ];

        $response = Http::withHeaders($headers)->post($url, $data);
        return response()->json(['status' => $response->status(), 'response' => $response->json()]);
    }
}
