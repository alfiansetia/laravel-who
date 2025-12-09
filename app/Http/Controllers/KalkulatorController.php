<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class KalkulatorController extends Controller
{
    function index()
    {
        $bcms = collect([
            new Breadcrumb('Kalkulator Nilai', route('kalkulator.index'), false),
        ]);
        $title = 'Kalkulator Nilai';
        return view('kalkulator.index', compact('title', 'bcms'));
    }
}
