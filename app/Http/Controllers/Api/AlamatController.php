<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DetailAlamat;
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
}
