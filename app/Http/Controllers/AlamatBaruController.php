<?php

namespace App\Http\Controllers;

use App\Models\AlamatBaru;
use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class AlamatBaruController extends Controller
{

    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Alamat Baru', route('alamat_baru.index'), false),
        ]);
        return view('alamat_baru.index', compact(['bcms']))->with(['title' => 'List Alamat Baru']);
    }

    public function create()
    {
        $bcms = collect([
            new Breadcrumb('List Alamat Baru', route('alamat_baru.index'), true),
            new Breadcrumb('Create Alamat Baru', route('alamat_baru.create'), false),
        ]);
        return view('alamat_baru.create', compact(['bcms']))->with(['title' => 'Create Alamat Baru']);
    }

    public function edit(AlamatBaru $alamatBaru)
    {
        $bcms = collect([
            new Breadcrumb('List Alamat Baru', route('alamat_baru.index'), true),
            new Breadcrumb($alamatBaru->do, route('alamat_baru.edit', $alamatBaru->id), false),
        ]);
        $data = $alamatBaru;
        return view('alamat_baru.edit', compact(['data', 'bcms']))->with(['title' => 'Edit Alamat Baru']);
    }

    public function show(Request $request, AlamatBaru $alamatBaru)
    {
        $data = $alamatBaru;
        $kolis = $alamatBaru->kolis()
            ->when($request->koli_id, function ($q) use ($request) {
                $q->where('id', $request->koli_id);
            })
            ->with('items.product')
            ->get();
        return view('alamat_baru.show', compact('data', 'kolis'))->with(['title' => 'Detail Alamat Baru']);
    }
}
