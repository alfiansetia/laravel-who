<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OdooSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $session = OdooSession::getCurrentSession();
        return $this->sendResponse($session);
    }

    public function store(Request $request)
    {
        $valid = Validator::make(
            $request->all(),
            ['env_value' => 'required']
        );
        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first(), 422);
        }
        $session = OdooSession::getCurrentSession();
        $session['session_id'] = $request->env_value;
        OdooSession::saveSession($session);
        return $this->sendResponse('Success!');
    }

    public function reload()
    {
        Artisan::call('app:odoo-login');
        return $this->sendResponse('Success!');
    }
}
