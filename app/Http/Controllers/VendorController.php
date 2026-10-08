<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Vendor/Index', [
            'title' => 'Vendor Odoo',
            'filters' => $request->only(['search', 'page']),
        ]);
    }
}
