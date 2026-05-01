<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlamatBaru;
use App\Models\Bast;
use App\Models\Koli;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class AlamatBaruController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    public function index()
    {
        $data = AlamatBaru::latest()->get();
        return $this->sendResponse($data);
    }

    public function show(AlamatBaru $alamatBaru)
    {
        $data = $alamatBaru->load('kolis.items.product');
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tujuan'    => 'required',
            'alamat'    => 'required',
            'do'        => 'required',
        ]);
        $totalKoli = $request->total_koli ?? 0;

        $param = [
            'tujuan'        => $request->tujuan,
            'alamat'        => $request->alamat,
            'ekspedisi'     => $request->ekspedisi,
            'total_koli'    => $totalKoli,
            'up'            => $request->up,
            'tlp'           => $request->tlp,
            'do'            => $request->do,
            'epur'          => $request->epur,
            'untuk'         => $request->untuk,
            'note'          => $request->note,
            'note_wh'       => $request->note_wh,
        ];
        $alamatBaru = AlamatBaru::create($param);
        if ($totalKoli > 0) {
            $urutan = $totalKoli == 1 ? "1" : "1-$totalKoli";
            Koli::create([
                'alamat_baru_id' => $alamatBaru->id,
                'urutan'         => $urutan,
                'order'          => $totalKoli,
                'is_asuransi'    => 'yes',
            ]);
        }
        return $this->sendResponse($alamatBaru->load('kolis'), 'Created!');
    }

    public function update(Request $request, AlamatBaru $alamatBaru)
    {
        $this->validate($request, [
            'tujuan'    => 'required',
            'alamat'    => 'required',
            'do'        => 'required',
        ]);

        $param = [
            'tujuan'        => $request->tujuan,
            'alamat'        => $request->alamat,
            'ekspedisi'     => $request->ekspedisi,
            'total_koli'    => $request->total_koli ?? 0,
            'up'            => $request->up,
            'tlp'           => $request->tlp,
            'do'            => $request->do,
            'epur'          => $request->epur,
            'untuk'         => $request->untuk,
            'note'          => $request->note,
            'note_wh'       => $request->note_wh,
        ];
        $alamatBaru->update($param);
        return $this->sendResponse($alamatBaru->load('kolis'), 'Updated!');
    }

    public function destroy(AlamatBaru $alamatBaru)
    {
        $alamatBaru->delete();
        return $this->sendResponse($alamatBaru, 'Deleted!');
    }

    public function duplicate(AlamatBaru $alamatBaru)
    {
        $data = $alamatBaru->replicate();
        $data->save();

        foreach ($alamatBaru->kolis as $koli) {
            $newKoli = $koli->replicate();
            $newKoli->alamat_baru_id = $data->id;
            $newKoli->save();

            foreach ($koli->items as $item) {
                $newItem = $item->replicate();
                $newItem->koli_id = $newKoli->id;
                $newItem->save();
            }
        }

        return $this->sendResponse($data, 'Success Duplicate!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:alamat_barus,id',
        ]);
        $deleted = AlamatBaru::whereIn('id', $request->ids)->delete();
        return $this->sendResponse([
            'deleted_count' => $deleted
        ], 'Alamat Baru deleted successfully.');
    }

    public function bast(AlamatBaru $alamatBaru)
    {
        $no_do = $alamatBaru->do;
        $bast = Bast::query()
            ->where('do', $no_do)
            ->first();
        if ($bast) {
            return $this->sendError('BAST Sudah ada!');
        }
        $response = DoServices::getAll($no_do, 1);
        $do = Arr::get($response, 'records.0', null);
        if (!$do) {
            return $this->sendError('DO tidak ditemukan!');
        }
        $bast = Bast::create([
            'do'        => $no_do,
            'address'   => Str::replace("\n", " ", $alamatBaru->alamat),
            'name'      => $alamatBaru->tujuan,
            'city'      => Arr::get($do, 'partner_address3', ''),
        ]);
        return $this->sendResponse($bast, 'Success Create BAST!');
    }
}
