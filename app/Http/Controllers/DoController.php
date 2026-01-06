<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use App\Services\DoServices;

class DoController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List DO', route('do.index'), false),
        ]);
        return view('do.index', compact('bcms'))->with('title', 'DO');
    }

    public function print(Request $request, $id)
    {
        $with_lot = $request->with_lot ?? false;
        $data = DoServices::detail($id);
        if (!isset($data['id'])) {
            return redirect()->route('do.index')->with('error', 'Data Tidak Ditemukan');
        }
        return view('do.print', compact('data', 'with_lot'));
    }
}
