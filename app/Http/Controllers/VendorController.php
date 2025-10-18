<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Vendor', route('vendors.index'), false),
        ]);
        return view('vendor.index', compact('bcms'));
    }
}
