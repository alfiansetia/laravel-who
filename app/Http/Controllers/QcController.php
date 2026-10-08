<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class QcController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();
        if ($now->isMonday()) {
            $date = $now->copy()->subDays(3)->toDateString();
        } else {
            $date = $now->copy()->subDay()->toDateString();
        }

        return Inertia::render('Qc/Index', [
            'title' => 'Form QC',
            'defaultDate' => $date,
        ]);
    }
}
