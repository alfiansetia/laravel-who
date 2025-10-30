<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseServices;
use App\Services\OdooSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['test_notif', 'store', 'reload']);
    }

    public function index()
    {
        $session = OdooSession::getCurrentSession();
        return $this->sendResponse($session);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'env_value' => 'required'
        ]);
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

    public function test_notif()
    {
        $serv = FirebaseServices::send('⚠️ Test!', 'Eh yaampun ini cuma test notif 😁✌️!');
        return $this->sendResponse('Success kirim notif ke semua perangkat!');
    }
}
