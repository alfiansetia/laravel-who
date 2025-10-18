<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SnController extends Controller
{
    public function index()
    {
        return view('sn.index')->with(['title' => 'Tool Sn']);
    }
}
