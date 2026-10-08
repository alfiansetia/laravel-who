<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RIController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Ri/Index', [
            'title' => 'RI',
            'filters' => $request->only(['search', 'page']),
        ]);
    }
}
