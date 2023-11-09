<?php

namespace App\Http\Controllers;

use App\Imports\KontakImport;
use App\Models\Kontak;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KontakController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'message' => '',
                'data' => Kontak::get(),
            ]);
        } else {
            return view('kontak.data')->with('title', 'Data Kontak');
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            // 'product'    => 'required|mimes:csv',
            'kontak'    => 'required|mimes:xls,xlsx',
        ]);
        // Product::truncate(); $request->file('file')->store('files')
        // Excel::import(new ProductImport, request()->file('product')->store('files'));
        try {
            Excel::import(new KontakImport, $request->file('kontak')->store('files'));
            return redirect()->back()->with('message', 'Data Imported Successfully');
        } catch (\Exception $e) {
            // return $e->getMessage();
            return redirect()->back()->with('message', $e->getMessage());
        }
    }

    public function show(Kontak $kontak)
    {
        return response()->json(['message' => '', 'data' => $kontak]);
    }


    public function destroy(Request $request)
    {
        if ($request->ajax()) {
            if ($request->id) {
                $count = count($request->id);
                $counter = 0;
                foreach ($request->id as $id) {
                    $kontak = Kontak::findOrFail($id);
                    $kontak->delete();
                    if ($kontak) {
                        $counter = $counter + 1;
                    }
                }
                return response()->json(['status' => true, 'message' => 'Success Delete ' . $count . '/' . $counter . ' Data', 'data' => '']);
            } else {
                return response()->json(['status' => false, 'message' => 'No Selected Data', 'data' => '']);
            }
        } else {
            abort(404);
        }
    }
}
