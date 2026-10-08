<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    public function stt(Request $request): Response
    {
        return Inertia::render('Stt/Index', [
            'title' => 'Speech To Text',
        ]);
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

    public function laporan_luarkota(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('Laporan Luarkota', route('tools.laporan_luarkota'), false),
        ]);

        return view('laporan_luarkota.index', compact('bcms'))
            ->with('title', 'Laporan Luarkota');
    }

    public function index()
    {
        $bcms = collect([
            new Breadcrumb('SN Tools', route('tools.sn'), false),
        ]);

        return view('sn.index', compact('bcms'))->with(['title' => 'Tool Sn']);
    }

    public function scoreboard(): Response
    {
        return Inertia::render('Scoreboard/Index', [
            'title' => 'Scoreboard',
        ]);
    }

    public function ocr(): Response
    {
        return Inertia::render('Ocr/Index', [
            'title' => 'OCR Tool',
        ]);
    }

    public function spreadsheet()
    {
        $title = 'Spreadsheet Tool';

        return view('spreadsheet.index', compact('title'));
    }

    public function print_resi()
    {
        $bcms = collect([
            new Breadcrumb('Print Resi', route('tools.print_resi'), false),
        ]);

        return view('print_resi.index', compact('bcms'))
            ->with(['title' => 'Print Resi']);
    }
}
