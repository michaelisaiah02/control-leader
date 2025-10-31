<?php

namespace App\Http\Controllers\ControlLeader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthly()
    {
        return view('control.reports.monthly');
    }
}
