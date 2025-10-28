<?php

namespace App\Http\Controllers;

use App\Models\Kargan;
use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;

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
        return view('kargan.create', compact('products', 'bcms'))->with(['title' => 'Create Kargan']);
    }

    public function edit(Kargan $kargan)
    {
        $products = Product::all();
        $data = $kargan->load('product');
        $bcms = collect([
            new Breadcrumb('List Kargan', route('kargans.index'), true),
            new Breadcrumb($data->number, route('kargans.edit', $data->id), false),
        ]);
        return view('kargan.edit', compact(['data', 'products', 'bcms']))->with(['title' => 'Edit Kargan']);
    }
}
