<?php

namespace App\Http\Controllers;

use App\Models\Bast;
use App\Models\Product;
use Illuminate\Http\Request;

class BastController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bast.index')->with(['title' => 'List BAST']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::all();
        return view('bast.create')->with(['title' => 'Create BAST']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bast  $bast
     * @return \Illuminate\Http\Response
     */
    public function edit(Bast $bast)
    {
        $products = Product::all();
        $data = $bast->load('details');
        return view('bast.edit', compact(['data', 'products']))->with(['title' => 'Create BAST']);
    }
}
