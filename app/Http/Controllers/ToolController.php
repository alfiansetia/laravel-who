<?php

namespace App\Http\Controllers;

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

    public function kalkulator(Request $request): Response
    {
        return Inertia::render('Kalkulator/Index', [
            'title' => 'Kalkulator Nilai',
        ]);
    }

    public function laporan_pengiriman(Request $request): Response
    {
        return Inertia::render('LaporanPengiriman/Index', [
            'title' => 'Laporan Pengiriman',
        ]);
    }

    public function laporan_luarkota(Request $request): Response
    {
        return Inertia::render('LaporanLuarkota/Index', [
            'title' => 'Laporan Luarkota',
        ]);
    }

    public function index(): Response
    {
        return Inertia::render('Sn/Index', [
            'title' => 'SN Tools',
        ]);
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

    public function spreadsheet(): Response
    {
        return Inertia::render('Spreadsheet/Index', [
            'title' => 'Spreadsheet PLTBB',
        ]);
    }

    public function print_resi(): Response
    {
        return Inertia::render('PrintResi/Index', [
            'title' => 'Print Resi',
        ]);
    }
}
