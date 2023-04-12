<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => '',
                'data' => Product::get(),
            ]);
        } else {
            return view('product.data')->with('title', 'Data Product');
        }
        //

    }

    public function getdata(Request $request)
    {
        $keyword = $request->q;
        $products = Product::where(function ($query) use ($keyword) {
                    $query->where('name', 'LIKE', '%'.$keyword.'%')->orWhere('code', 'LIKE', '%'.$keyword.'%');
                })->paginate(20);
        return response()->json($products, 200);
        // return response()->json([
            // 'status' => true,
            // 'message' => '',
            // 'data' => Product::get(),
        // ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'product'    => 'required|mimes:csv',
            // 'product'    => 'required|mimes:xls,xlsx',
        ]);
        // Product::truncate(); $request->file('file')->store('files')
        // Excel::import(new ProductImport, request()->file('product')->store('files'));
        try {
            Excel::import(new ProductImport, $request->file('product')->store('files'));
        } catch (\Exception $e) {
            // return $e->getMessage();
            return redirect()->back()->with('message', $e->getMessage());
        }
        return redirect()->back()->with('message', 'Data Imported Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {
        if ($request->ajax()) {
            if ($request->id) {
                $count = count($request->id);
                $counter = 0;
                foreach ($request->id as $id) {
                    $product = Product::findOrFail($id);
                    $product->delete();
                    if ($product) {
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
