<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = auth()->user()->role;
        // Cek user
        if ($role !== 'management' && $role !== 'ypq') {

            dd($role);
            return redirect()->back()->with('error', 'Tidak ada izin akses!');
        }

        return $next($request);
    }
}
