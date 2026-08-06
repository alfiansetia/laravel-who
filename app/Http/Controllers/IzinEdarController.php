<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class IzinEdarController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('Izin Edar', route('izin_edars.index'), false),
        ]);

        $kategoriList = ['AKD', 'AKL', 'PKD', 'PKL', 'Lainnya'];

        return view('izin_edar.index', compact('bcms', 'kategoriList'))->with('title', 'Data Izin Edar');
    }
}
