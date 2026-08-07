<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use App\Services\ItServices;
use Illuminate\Http\Request;

class ItController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List IT', route('it.index'), false),
        ]);
        return view('it.index', compact('bcms'))->with('title', 'IT');
    }

    public function print(Request $request, $id)
    {
        $data = ItServices::detail($id);
        if (!isset($data['id'])) {
            return redirect()->route('it.index')->with('error', 'Data Tidak Ditemukan');
        }
        return view('it.print', compact('data'));
    }
}
