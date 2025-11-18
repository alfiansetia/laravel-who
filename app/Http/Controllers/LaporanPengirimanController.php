<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class LaporanPengirimanController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('Laporan Pengiriman', route('laporan_pengiriman.index'), false),
        ]);
        return view('laporan_pengiriman.index', compact(['bcms']))
            ->with(['title' => 'Laporan Pengiriman']);
    }
}
