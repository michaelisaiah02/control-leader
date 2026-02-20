<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Menampilkan form login berdasarkan aplikasi yang dipilih.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Memproses upaya login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'employeeID' => 'required|string',
            'password' => 'required|string',
            'shift' => 'required|in:1,2,3',
        ]);

        $credentials = $request->only('employeeID', 'password');

        $guard = Auth::guard('web');

        if ($guard->attempt($credentials)) {
            $request->session()->regenerate();

            // >>> Tambahan: single-device hanya untuk control_leader
            $user = $guard->user();
            if (! $user->can_login) {
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages(['error' => 'Akun ini tidak diizinkan login.']);
            }
            $sid = $request->session()->getId();
            $request->session()->put('shift', $request->input('shift'));

            // LOCK: hanya block takeover kalau user masih mengisi (ping < 3 menit)
            $LOCK_TTL_MIN = 3;
            $lockActive = $user->cl_in_progress
                && $user->cl_last_ping
                && now()->diffInMinutes($user->cl_last_ping) < $LOCK_TTL_MIN;

            if (! empty($user->control_session_id) && $user->control_session_id !== $sid && $lockActive) {
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'error' => 'Akun CONTROL LEADER sedang mengisi checksheet di perangkat lain.',
                ]);
            }

            // takeover OK (kalau tidak sedang aktif)
            $user->forceFill([
                'control_session_id' => $sid,
                'cl_in_progress' => false,     // reset flag nyangkut
                'cl_last_ping' => now(),
            ])->save();

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'error' => 'Employee ID atau Password salah untuk aplikasi ini.',
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['active_app', 'login_app_type', 'url.intended', 'cl_in_progress', 'shift']);

        if (auth()->check()) {
            auth()->user()
                ?->forceFill(['control_session_id' => null, 'cl_in_progress' => false])
                ->save();
        }

        Auth::logout();
        Auth::guard('web')->logout();
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Arahkan ke halaman login netral
        return redirect('/');
    }
}
