<?php

namespace App\Http\Middleware;

use App\Models\ControlLeader\ChecksheetDraft;
use Closure;
use Illuminate\Http\Request;

class RedirectToActiveDraft
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('web_control_leader')->user();
        if (! $user) {
            return $next($request);
        }

        // jangan loop redirect sendiri
        if ($request->routeIs('control.checksheets.*')) {
            return $next($request);
        }

        // cek draft aktif
        $draft = ChecksheetDraft::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        if ($draft) {
            return redirect()->route('control.checksheets.create', [
                'detail' => $draft->schedule_detail_id,
                'type' => $draft->phase,
            ]);
        }

        return $next($request);
    }
}
