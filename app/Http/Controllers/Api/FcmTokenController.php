<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function index(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $query = FcmToken::query();

        $recordsTotal = $query->count();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('platform', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%")
                  ->orWhere('token', 'like', "%{$search}%")
                  ->orWhere('last_status', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'platform', 'user_agent', 'ip', 'token', 'last_status'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $query->orderBy($orderColumn, $orderDir);

        $data = $query->skip($start)->take($length > 0 ? $length : 10)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'token'     => 'required',
            'topic'     => 'nullable',
            'platform'  => 'nullable',
        ]);
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        $token = FcmToken::query()->updateOrCreate(
            [
                'token' => $request->token,
            ],
            [
                'token'         => $request->token,
                'topic'         => $request->topic,
                'user_agent'    => $userAgent,
                'ip'            => $ip,
                'platform'      => $request->platform,
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
