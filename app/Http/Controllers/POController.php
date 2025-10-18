<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class POController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List PO', route('po.index'), false),
        ]);
        return view('po.index', compact('bcms'))->with('title', 'PO');
    }
}
