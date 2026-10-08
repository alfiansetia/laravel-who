<?php

namespace App\Http\Controllers;

use App\Services\DoServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Do/Index', [
            'title' => 'DO',
            'filters' => $request->only(['search', 'page', 'note_search']),
        ]);
    }

    public function print(Request $request, $id)
    {
        $with_lot = $request->with_lot ?? false;
        $data = DoServices::detail($id);
        if (! isset($data['id'])) {
            return redirect()->route('do.index')->with('error', 'Data Tidak Ditemukan');
        }

        return view('do.print', compact('data', 'with_lot'));
    }
}
