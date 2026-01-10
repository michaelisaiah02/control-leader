<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SingleLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $u = auth('web_control_leader')->user();
        if ($u) {
            $sid = $request->session()->getId();
            $lockActive = $u->cl_in_progress && $u->cl_last_ping
                && now()->diffInMinutes($u->cl_last_ping) < 3;

            if ($u->control_session_id !== $sid) {
                if ($lockActive) {
                    // lagi ngisi di tempat lain → tendang
                    auth('web_control_leader')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'error' => 'Sesi kamu tidak valid (akun sedang dipakai untuk checksheet).',
                    ]);
                } else {
                    // tidak ngisi → adopsi sesi baru
                    $u->forceFill(['control_session_id' => $sid])->save();
                }
            }
        }

        return $next($request);
    }
}
