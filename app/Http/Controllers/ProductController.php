<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return Inertia::render('Product/Index', [
            'title' => 'Data Product',
            'filters' => $request->only(['search']),
        ]);
    }
}
