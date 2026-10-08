<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LotController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Lot/Index', [
            'title' => 'Data Lot',
            'filters' => $request->only(['search', 'page', 'product']),
        ]);
    }
}
