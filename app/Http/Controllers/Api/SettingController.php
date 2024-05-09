<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function set_env(Request $request)
    {
        $valid = Validator::make(
            $request->all(),
            ['env_value' => 'required']
        );
        if ($valid->fails()) {
            return response()->json(['message' => $valid->getMessageBag()], 422);
        }
        $setting = Setting::first();
        if (!$setting) {
            Setting::create([
                'odoo_session' => $request->env_value
            ]);
        } else {
            $setting->update([
                'odoo_session' => $request->env_value
            ]);
        }
        return response()->json(['message' => 'Success!']);
    }
}
