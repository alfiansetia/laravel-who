<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Product Stock', route('stock.index'), false),
        ]);
        return view('stock.index', compact('bcms'))->with(['title' => 'Data Stock']);
    }
}
