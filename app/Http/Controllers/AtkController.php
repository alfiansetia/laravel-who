<?php

namespace App\Http\Controllers;

use App\Models\Atk;
use Illuminate\Http\Request;

class AtkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('atk.index')->with(['title' => 'Data ATK']);
    }

    public function import()
    {
        return view('atk.import')->with(['title' => 'Import Data ATK']);
    }
}
