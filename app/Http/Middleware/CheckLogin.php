<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek aplikasi apa yang aktif dari session
        $activeApp = $request->session()->get('active_app');

        // 2. Tentukan guard mana yang harus digunakan
        $guard = $activeApp === 'control_leader' ? 'web_control_leader' : 'web';
        // Cek apakah user sudah login
        if (auth()->guard($guard)->check()) {
            // Jika belum, lempar ke halaman login
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
