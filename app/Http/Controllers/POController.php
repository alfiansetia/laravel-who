<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class POController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Po/Index', [
            'title' => 'PO',
            'filters' => $request->only(['search', 'page']),
        ]);
    }
}
