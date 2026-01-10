<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAppAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek aplikasi apa yang aktif dari session
        $activeApp = $request->session()->get('active_app');

        // 2. Tentukan guard mana yang harus digunakan
        $guard = $activeApp === 'control_leader' ? 'web_control_leader' : 'web';

        // 3. Periksa apakah user sudah login MENGGUNAKAN GUARD YANG BENAR
        if (! Auth::guard($guard)->check()) {
            // 4. Jika belum, lempar ke halaman login
            return redirect()->route('welcome');
        }

        // 5. Jika sudah, lanjutkan ke halaman yang dituju (misal: dashboard)
        return $next($request);
    }
}
