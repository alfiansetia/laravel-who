<?php

namespace App\Http\Controllers;

use App\Models\Bast;
use App\Models\Product;
use App\Services\Breadcrumb;
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
        $bcms = collect([
            new Breadcrumb('List BAST', route('basts.index'), false),
        ]);
        return view('bast.index', compact('bcms'))->with(['title' => 'List BAST']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bcms = collect([
            new Breadcrumb('List BAST', route('basts.index'), true),
            new Breadcrumb('Create BAST', route('basts.create'), false),
        ]);
        $products = Product::all();
        return view('bast.create', compact('bcms'))->with(['title' => 'Create BAST']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bast  $bast
     * @return \Illuminate\Http\Response
     */
    public function edit(Bast $bast)
    {
        $bcms = collect([
            new Breadcrumb('List BAST', route('basts.index'), true),
            new Breadcrumb($bast->do, route('basts.edit', $bast->do), false),
        ]);
        $products = Product::all();
        $data = $bast->load('details');
        return view('bast.edit', compact(['data', 'products', 'bcms']))->with(['title' => 'Edit BAST']);
    }
}
