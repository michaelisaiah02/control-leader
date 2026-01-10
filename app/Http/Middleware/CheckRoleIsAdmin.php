<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = auth()->user()->role;
        // Cek apakah user adalah admin
        if ($role !== 'admin' || $role !== 'management' || $role !== 'ypq') {

            return redirect()->back()->with('error', 'Tidak ada izin akses!');
        }

        return $next($request);
    }
}
