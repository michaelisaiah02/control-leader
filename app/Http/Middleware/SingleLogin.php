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
        $guard = auth('web_control_leader');

        if ($guard->check()) {
            $user = $guard->user();
            $sid = $request->session()->getId();

            if (empty($user->control_session_id) || $user->control_session_id !== $sid) {
                // tendang kalau sesi tidak sah (mis. user login di device lain)
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'error' => 'Sesi control leader tidak valid (mungkin aktif di perangkat lain).',
                ]);
            }
        }

        return $next($request);
    }
}
