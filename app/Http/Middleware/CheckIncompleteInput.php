<?php

namespace App\Http\Middleware;

use App\Models\IncompleteInput;
use Closure;
use Illuminate\Http\Request;

class CheckIncompleteInput
{
    public function handle(Request $request, Closure $next)
    {
        $incomplete = IncompleteInput::forCurrentUser()->first();

        if ($incomplete) {
            switch ($incomplete->stage) {
                case 'standard':
                    if (! $request->routeIs('dashboard') && ! $request->routeIs('kalibrasi.standards.store')) {
                        return redirect()->route('dashboard')->with('warning', 'Please complete the standard input first.');
                    }
                    break;
                case 'calibration':
                    if (! $request->routeIs('kalibrasi.input.calibration.data') && ! $request->routeIs('kalibrasi.store.calibration')) {
                        return redirect()->route('kalibrasi.input.calibration.data')->with('warning', 'Please complete the calibration input first.');
                    }
                    break;
            }
        }

        return $next($request);
    }
}
