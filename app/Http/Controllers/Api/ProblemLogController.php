<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProblemLog;
use Illuminate\Http\Request;

class ProblemLogController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'problem_id' => 'required|exists:problems,id',
            'date' => 'required|date',
            'desc' => 'nullable|string',
        ]);

        $log = ProblemLog::create([
            'problem_id' => $request->problem_id,
            'date' => $request->date,
            'desc' => $request->desc,
        ]);

        return response()->json([
            'message' => 'Problem log created successfully',
            'data' => $log,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProblemLog $problemLog)
    {
        return response()->json([
            'data' => $problemLog,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProblemLog $problemLog)
    {
        $request->validate([
            'date' => 'sometimes|date',
            'desc' => 'nullable|string',
        ]);

        $problemLog->update($request->only(['date', 'desc']));

        return response()->json([
            'message' => 'Problem log updated successfully',
            'data' => $problemLog,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProblemLog $problemLog)
    {
        $problemLog->delete();

        return response()->json([
            'message' => 'Problem log deleted successfully',
        ]);
    }
}
