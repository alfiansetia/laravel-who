<?php

namespace App\Http\Controllers;

use App\Models\Kargan;
use App\Models\Product;
use App\Services\Breadcrumb;

class KarganController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Kargan', route('kargans.index'), false),
        ]);
        return view('kargan.index', compact('bcms'))->with(['title' => 'List Kargan']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bcms = collect([
            new Breadcrumb('List Kargan', route('kargans.index'), true),
            new Breadcrumb('Create Kargan', route('kargans.create'), false),
        ]);
        $products = Product::all();
        $last = Kargan::latest()->first();
        $new_number = Kargan::generateNumber();
        $last_number = $last ? $last->number : '-';
        return view('kargan.create', compact('products', 'bcms', 'new_number', 'last_number'))->with(['title' => 'Create Kargan']);
    }

    public function edit(Kargan $kargan)
    {
        $products = Product::all();
        $data = $kargan->load('product');
        $bcms = collect([
            new Breadcrumb('List Kargan', route('kargans.index'), true),
            new Breadcrumb($data->number, route('kargans.edit', $data->id), false),
        ]);
        $last = Kargan::whereNot('id', $kargan->id)->latest()->first();
        $last_number = $last ? $last->number : '-';
        return view('kargan.edit', compact(['data', 'products', 'bcms', 'last_number']))->with(['title' => 'Edit Kargan']);
    }
}
