<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Stock/Index', [
            'title' => 'Data Stock',
            'filters' => $request->only(['search', 'location']),
        ]);
    }
}
