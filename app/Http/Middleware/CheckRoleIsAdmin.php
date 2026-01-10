<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek aplikasi apa yang aktif dari session
        $activeApp = $request->session()->get('active_app');
        $guard = $activeApp === 'control_leader' ? 'web_control_leader' : 'web';
        // Cek apakah user adalah admin
        if (auth()->guard($guard)->user()->role !== 'admin') {
            if ($activeApp === 'control_leader') {
                if (auth()->guard('web_control_leader')->user()->role !== 'management' && auth()->guard('web_control_leader')->user()->role !== 'ypq') {
                    return redirect()->back()->with('error', 'Tidak ada izin akses!');
                }
            }

            return redirect()->back()->with('error', 'Khusus admin!');
        }

        return $next($request);
    }
}
