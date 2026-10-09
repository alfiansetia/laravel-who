<?php

namespace App\Http\Controllers;

use App\Models\Bast;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class BastController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return Inertia::render('Bast/Index', [
            'title' => 'List BAST',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();

        return Inertia::render('Bast/Create', [
            'title' => 'Create BAST',
            'products' => $products,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Bast $bast)
    {
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();
        $data = $bast->load('details.product');

        return Inertia::render('Bast/Edit', [
            'title' => 'Edit BAST',
            'bast' => $data,
            'products' => $products,
        ]);
    }

    public function print(Request $request, Bast $bast)
    {
        $type = $request->input('type', 'tanda_terima');
        $data = $bast->load('details');
        if ($type == 'tanda_terima') {
            return view('bast.print.tanda_terima', compact(['data', 'type']));
        } elseif ($type == 'training') {
            return view('bast.print.training', compact(['data', 'type']));
        } else {
            return view('bast.print.bast', compact(['data', 'type']));
        }
    }
}
