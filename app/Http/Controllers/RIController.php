<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RIController extends Controller
{
    public function index(Request $request)
    {
        return view('ri.index')->with('title', 'RI');
    }
}
