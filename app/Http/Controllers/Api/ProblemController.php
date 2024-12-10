<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Problem;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Problem::all();
        return response()->json(['data' => $data]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Problem $problem)
    {
        return response()->json(['data' => $problem->load(['items.product', 'logs'])]);
    }
}
