<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ControlLeader\ChecksheetDraft;
use Symfony\Component\HttpFoundation\Response;

class ResumeDraft
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth('web_control_leader')->user();
        if (!$user)
            return $next($request);

        // Hindari loop kalau sudah di route checksheet
        if (
            $request->routeIs('control.checksheets.*') ||
            $request->is('control/details/*/checksheets/*')
        ) {
            return $next($request);
        }

        // Tanpa TTL: selama is_active = 1, auto-resume
        $draft = ChecksheetDraft::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        if ($draft) {
            // takeover sesi sekarang, JANGAN ubah started_at
            $draft->forceFill([
                'session_id' => $request->session()->getId(),
                'last_ping' => now(),
            ])->save();

            return redirect()->route('control.checksheets.partA', [
                'detail' => $draft->schedule_detail_id,
                'type' => $draft->phase,
            ]);
        }

        return $next($request);
    }
}
