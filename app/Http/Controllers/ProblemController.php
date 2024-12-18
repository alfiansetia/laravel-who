<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('problem.index')->with('title', 'Data Problem');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('problem.create')->with('title', 'Data Problem');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $problem = Problem::create($request->only([
            'date',
            'number',
            'type',
            'stock',
            'ri_po',
            'status',
            'email_on',
            'pic',
        ]));
        return redirect()->route('problem.edit', $problem->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Problem $problem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Problem $problem)
    {
        $data = $problem->load(['items.product', 'logs']);
        return view('problem.edit', compact('data'))->with('title', 'Data Problem');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Problem $problem)
    {
        $problem->update($request->only([
            'date',
            'number',
            'type',
            'stock',
            'ri_po',
            'status',
            'email_on',
            'pic',
        ]));
        return redirect()->route('problem.edit', $problem->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Problem $problem)
    {
        //
    }
}
