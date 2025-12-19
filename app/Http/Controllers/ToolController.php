<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function stt(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('Speech To Text', route('tools.stt'), false),
        ]);
        return view('stt.index', compact('bcms'))
            ->with('title', 'Speech To Text');
    }

    public function kalkulator(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('Kalkulator Nilai', route('tools.kalkulator'), false),
        ]);
        return view('kalkulator.index', compact('bcms'))
            ->with('title', 'Kalkulator Nilai');
    }

    public function laporan_pengiriman(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('Laporan Pengiriman', route('tools.laporan_pengiriman'), false),
        ]);
        return view('laporan_pengiriman.index', compact('bcms'))
            ->with('title', 'Laporan Pengiriman');
    }

    public function index()
    {
        $bcms = collect([
            new Breadcrumb('SN Tools', route('tools.sn'), false),
        ]);
        return view('sn.index', compact('bcms'))->with(['title' => 'Tool Sn']);
    }

    public function scoreboard()
    {
        $title = 'Scoreboard';
        return view('scoreboard.index', compact('title'));
    }
}
