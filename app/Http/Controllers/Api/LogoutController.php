<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {        
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
        if($removeToken) {
            return response()->json([
                'status' => true,
                'message' => 'Logout Berhasil!',  
            ]);
        }
    }
}
