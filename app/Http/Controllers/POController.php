<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class POController extends Controller
{
    public function index(Request $request)
    {
        return view('po.index')->with('title', 'PO');
    }
}
