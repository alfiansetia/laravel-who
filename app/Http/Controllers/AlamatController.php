<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class AlamatController extends Controller
{

    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Alamat', route('alamats.index'), false),
        ]);
        return view('alamat.index', compact(['bcms']))->with(['title' => 'List Alamat']);
    }

    public function create()
    {
        $bcms = collect([
            new Breadcrumb('List Alamat', route('alamats.index'), true),
            new Breadcrumb('Create Alamat', route('alamats.create'), false),
        ]);
        return view('alamat.create', compact(['bcms']))->with(['title' => 'Create Alamat']);
    }

    public function edit(Alamat $alamat)
    {
        $bcms = collect([
            new Breadcrumb('List Alamat', route('alamats.index'), true),
            new Breadcrumb($alamat->do, route('alamats.edit', $alamat->id), false),
        ]);
        $data = $alamat;
        $products = Product::all();
        return view('alamat.edit', compact(['data', 'products', 'bcms']))->with(['title' => 'Edit Alamat']);
    }

    public function show(Alamat $alamat)
    {
        $data = $alamat;
        return view('alamat.show', compact('data'))->with(['title' => 'Detail Alamat']);
    }
}
