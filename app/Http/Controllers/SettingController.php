<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use App\Services\OdooSession;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('App Setting', route('settings.index'), false),
        ]);
        $data = OdooSession::getCurrentSession();
        return view('setting.index', compact(['data', 'bcms']))->with('title', 'App Setting');
    }
}
