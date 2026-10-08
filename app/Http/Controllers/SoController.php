<?php

namespace App\Http\Controllers;

use App\Services\SoServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SoController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('So/Index', [
            'title' => 'SO',
            'filters' => $request->only(['search', 'page', 'note_search', 'filter']),
        ]);
    }

    public function print($id)
    {
        $data = SoServices::detail($id);
        if (! isset($data['id'])) {
            return redirect()->route('so.index')->with('error', 'Data Tidak Ditemukan');
        }

        return view('so.print', compact('data'));
    }
}
