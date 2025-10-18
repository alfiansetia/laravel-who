<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Kontak', route('kontak.index'), false),
        ]);
        return view('kontak.data', compact('bcms'));
    }
}
