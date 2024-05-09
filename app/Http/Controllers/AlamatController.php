<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\Product;
use Illuminate\Http\Request;

class AlamatController extends Controller
{

    public function index()
    {
        return view('alamat.index')->with(['title' => 'List Alamat']);
    }

    public function create()
    {
        return view('alamat.create')->with(['title' => 'Create Alamat']);
    }

    public function edit(Alamat $alamat)
    {
        $data = $alamat;
        $products = Product::all();
        return view('alamat.edit', compact('data', 'products'))->with(['title' => 'Edit Alamat']);
    }

    public function show(Alamat $alamat)
    {
        $data = $alamat;
        return view('alamat.show', compact('data'))->with(['title' => 'Detail Alamat']);
    }
}
