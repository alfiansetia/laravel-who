<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\DetailAlamat;
use App\Models\Product;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    public function create(Request $request)
    {
        $data = Alamat::first();
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
}
