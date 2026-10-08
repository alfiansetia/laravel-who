<?php

namespace App\Http\Controllers;

use App\Services\OdooSession;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth');
    }

    public function index(): Response
    {
        return Inertia::render('Setting/Index', [
            'title' => 'App Setting',
            'session' => OdooSession::getCurrentSession(),
        ]);
    }
}
