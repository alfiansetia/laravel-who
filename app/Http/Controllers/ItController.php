<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class ItController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List IT', route('it.index'), false),
        ]);
        return view('it.index', compact('bcms'))->with('title', 'IT');
    }
}
