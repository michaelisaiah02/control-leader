<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckResult;

abstract class Controller extends \Illuminate\Routing\Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware can be applied here if needed
        if (url()->current() !== url('/input/calibration-data') && auth()->check()) {
            $this->middleware(CheckResult::class);
        }
    }
}
