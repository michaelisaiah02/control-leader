<?php

namespace App\Http\Middleware;

use App\Models\ChecksheetDraft;
use Closure;
use Illuminate\Http\Request;

class ResumeDraft
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (! $user) {
            return $next($request);
        }

        // Hindari loop kalau sudah di route checksheet
        if (
            $request->routeIs('checksheets.*') ||
            $request->is('details/*/checksheets/*')
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

            return redirect()->route('checksheets.create', [
                'detail' => $draft->schedule_detail_id,
                'type' => $draft->phase,
            ]);
        }

        return $next($request);
    }
}
