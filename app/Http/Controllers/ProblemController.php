<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy('code')->get();
        return view('problem.index', compact('products'))->with('title', 'Data Problem');
    }

    public function import()
    {
        $bcms = collect([
            new Breadcrumb('Problem', route('problems.index'), true),
            new Breadcrumb('Import', route('problems.import'), false),
        ]);
        return view('problem.import', compact('bcms'));
    }
}
