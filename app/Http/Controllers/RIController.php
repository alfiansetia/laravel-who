<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class RIController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List RI', route('ri.index'), false),
        ]);
        return view('ri.index', compact('bcms'))->with('title', 'RI');
    }
}
