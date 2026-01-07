<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QcLot;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class QcLotController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('QC Lot', route('qc_lots.index'), false),
        ]);
        $products = Product::all();
        return view('qc_lot.index', compact('bcms', 'products'));
    }

    public function import()
    {
        $bcms = collect([
            new Breadcrumb('QC Lot', route('qc_lots.index'), true),
            new Breadcrumb('Import', route('qc_lots.import'), false),
        ]);
        return view('qc_lot.import', compact('bcms'));
    }
}
