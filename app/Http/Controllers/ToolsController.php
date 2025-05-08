<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function sn()
    {
        return view('tools.sn')->with(['title' => 'Tool Sn']);
    }
}
