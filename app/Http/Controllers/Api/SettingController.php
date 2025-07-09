<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\OdooSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $session = OdooSession::getCurrentSession();
        return response()->json([
            'data' => $session
        ]);
    }

    public function set_env(Request $request)
    {
        $valid = Validator::make(
            $request->all(),
            ['env_value' => 'required']
        );
        if ($valid->fails()) {
            return response()->json(['message' => $valid->getMessageBag()], 422);
        }
        $session = OdooSession::getCurrentSession();
        $session['session_id'] = $request->env_value;
        OdooSession::saveSession($session);
        return response()->json(['message' => 'Success!']);
    }

    public function reload()
    {
        Artisan::call('app:odoo-login');
        return response()->json(['message' => 'Success!']);
    }
}
