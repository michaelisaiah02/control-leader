<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveChecksheet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd(session()->all());
        // Cek apakah user sedang dalam mode pengerjaan checksheet
        if (session()->has('active_checksheet')) {
            $active = session('active_checksheet');

            // Daftar rute yang DIKEKECUALIKAN dari penguncian
            $allowedRoutes = [
                'checksheets.create',
                'checksheets.partB',
                'checksheets.store',
                'logout', // Izinkan kalau dia mau nyerah dan logout
            ];

            // Kalau user nyoba ngakses rute selain di atas (misal klik dashboard / ganti URL)
            if (! $request->routeIs($allowedRoutes)) {

                // (Opsional) Kasih notif/toast ngomel
                session()->flash('infoNotification', 'Selesaikan Checksheet dulu sebelum mengakses halaman lain.');

                // Lempar balik ke Part A (dengan phase yang lagi dia kerjain)
                return redirect()->route('checksheets.create', ['type' => $active['phase']]);
            }
        }

        return $next($request);
    }
}
