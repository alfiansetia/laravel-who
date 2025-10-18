<?php

namespace App\Http\Controllers;

use App\Models\Atk;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class AtkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List ATK', route('atk.index'), false),
        ]);
        return view('atk.index', compact('bcms'))->with(['title' => 'Data ATK']);
    }

    public function import()
    {
        $bcms = collect([
            new Breadcrumb('List ATK', route('atk.index'), true),
            new Breadcrumb('Import ATK', route('atk.import'), false),
        ]);
        return view('atk.import', compact('bcms'))->with(['title' => 'Import Data ATK']);
    }

    public function eksport(Atk $atk)
    {
        $bcms = collect([
            new Breadcrumb('List ATK', route('atk.index'), true),
            new Breadcrumb('Eksport ATK', route('atk.eksport', $atk->id), false),
        ]);
        // $data = Atk::with('transactions')->get();
        $data = $atk->load('transactions');
        return view('atk.export', compact('data', 'bcms'))->with(['title' => 'Import Data ATK']);
    }
}
