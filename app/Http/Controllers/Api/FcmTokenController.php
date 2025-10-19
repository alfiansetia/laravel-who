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
        return $this->sendResponse($data, 'Success Get Token');
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
        return $this->sendResponse($token, 'Success Upsert Token');
    }

    public function show(FcmToken $token)
    {
        return $this->sendResponse($token, 'Success Get Token');
    }

    public function destroy(FcmToken $token)
    {
        $token->delete();
        return $this->sendResponse($token, 'Success Delete Token');
    }
}
