<?php

namespace App\Http\Controllers;

use App\Services\ItServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('It/Index', [
            'title' => 'IT',
            'filters' => $request->only(['search', 'page', 'gudang']),
        ]);
    }

    public function print(Request $request, $id)
    {
        $data = ItServices::detail($id);
        if (! isset($data['id'])) {
            return redirect()->route('it.index')->with('error', 'Data Tidak Ditemukan');
        }

        return view('it.print', compact('data'));
    }
}
