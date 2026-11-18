<?php

namespace App\Http\Controllers;

use App\Services\LndServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LndController extends Controller
{
    public function index(Request $request, LndServices $lnd)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $lnd->getPrograms(),
            ]);
        }

        return Inertia::render('Lnd/Index', [
            'title' => 'L&D',
        ]);
    }
}
