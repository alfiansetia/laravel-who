<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductOdooController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('ProductOdoo/Index', [
            'title' => 'Product Odoo',
            'filters' => $request->only(['search', 'page']),
        ]);
    }
}
