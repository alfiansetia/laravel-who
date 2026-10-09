<?php

namespace App\Http\Controllers;

use App\Models\AlamatBaru;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlamatBaruController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('AlamatBaru/Index', [
            'title' => 'List Alamat Baru',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('AlamatBaru/Create', [
            'title' => 'Create Alamat Baru',
        ]);
    }

    public function edit(AlamatBaru $alamatBaru)
    {
        $data = $alamatBaru;
        $products = Product::query()
            ->select('id', 'code', 'name')
            ->orderBy('code')
            ->get();

        return Inertia::render('AlamatBaru/Edit', [
            'title' => 'Edit Alamat Baru',
            'record' => $data,
            'products' => $products,
        ]);
    }

    public function show(Request $request, AlamatBaru $alamatBaru)
    {
        $is_split = $request->boolean('split') ?? false;
        $data = $alamatBaru;
        $kolis = $alamatBaru->kolis()
            ->when($request->koli_id, function ($q) use ($request) {
                $q->where('id', $request->koli_id);
            })
            ->with('items.product')
            ->get();

        return view('alamat_baru.show', compact('data', 'kolis', 'is_split'))->with(['title' => 'Detail Alamat Baru']);
    }
}
