<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sop;
use App\Models\SopItem;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List SOP QC', route('sops.index'), false),
        ]);
        return view('sop.index', compact('bcms'));
    }

    public function create()
    {
        $bcms = collect([
            new Breadcrumb('List SOP QC', route('sops.index'), true),
            new Breadcrumb('Manage SOP QC', route('sops.create'), false),
        ]);
        $products = Product::all();
        $sop_items = SopItem::query()
            ->select('item')
            ->distinct()
            ->get();
        return view('sop.create', compact('products', 'bcms', 'sop_items'));
    }
}
