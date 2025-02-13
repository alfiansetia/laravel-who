<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function index()
    {
        $data = FcmToken::all();
        return response()->json([
            'message' => 'success Get Token',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'topic' => 'nullable',
        ]);
        $token = FcmToken::query()->updateOrCreate(
            [
                'token' => $request->token,
            ],
            [
                'token' => $request->token,
                'topic' => $request->topic,
            ]
        );
        return response()->json([
            'message' => 'success Upsert Token',
            'data' => $token
        ]);
    }

    public function show(FcmToken $token)
    {
        return response()->json([
            'message' => 'success Get Token',
            'data' => $token
        ]);
    }

    public function destroy(FcmToken $token)
    {
        $token->delete();
        return response()->json([
            'message' => 'success Delete Token',
            'data' => $token
        ]);
    }
}
