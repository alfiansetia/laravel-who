<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class SnController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('SN Tools', route('sn.index'), false),
        ]);
        return view('sn.index', compact('bcms'))->with(['title' => 'Tool Sn']);
    }
}
