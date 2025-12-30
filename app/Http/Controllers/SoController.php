<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class SoController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List SO', route('so.index'), false),
        ]);
        return view('so.index', compact('bcms'))->with('title', 'SO');
    }
}
