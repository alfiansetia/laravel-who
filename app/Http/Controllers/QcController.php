<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Breadcrumb;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QcController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('Form QC', route('qc.index'), false),
        ]);
        $products = Product::all();
        $now = Carbon::now();
        if ($now->isMonday()) {
            $date = $now->subDays(3)->toDateString();
        } else {
            $date = $now->subDay()->toDateString();
        }

        return view('qc.index', compact('products', 'bcms', 'date'))->with('title', 'Form QC');
    }
}
