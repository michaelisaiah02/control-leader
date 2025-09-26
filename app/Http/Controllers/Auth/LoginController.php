<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ControlLeader\ChecksheetDraft;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Menampilkan form login berdasarkan aplikasi yang dipilih.
     */
    public function showLoginForm(Request $request)
    {
        // Jika ada parameter 'app' di URL (saat user pertama kali memilih)
        if ($request->has('app')) {
            // Simpan pilihan aplikasi ke dalam session sementara
            $request->session()->put('login_app_type', $request->query('app'));
        }

        // Ambil pilihan aplikasi dari session
        $appType = $request->session()->get('login_app_type');

        // Jika session-nya pun tidak ada, baru lempar ke welcome
        if (!$appType || !in_array($appType, ['kalibrasi', 'control_leader'])) {
            return redirect()->route('welcome');
        }

        $appName = match ($appType) {
            'kalibrasi' => 'KALIBRASI',
            'control_leader' => 'CONTROL LEADER',
            default => 'APPLICATION'
        };

        return view('auth.login', [
            'appName' => $appName,
            'appType' => $appType,
        ]);
    }

    /**
     * Memproses upaya login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'employeeID' => 'required|string',
            'password' => 'required|string',
            'app' => 'required|string|in:kalibrasi,control_leader',
        ]);

        $credentials = $request->only('employeeID', 'password');
        $activeApp = $request->input('app');

        // Pilih guard mana yang akan digunakan untuk otentikasi
        $guard = $activeApp === 'control_leader'
            ? Auth::guard('web_control_leader')
            : Auth::guard('web');

        if ($guard->attempt($credentials)) {
            $request->session()->regenerate();
            $request->session()->put('active_app', $activeApp);
            $request->session()->forget(['login_app_type', 'url.intended']);

            // >>> Tambahan: single-device hanya untuk control_leader
            if ($activeApp === 'control_leader') {
                $user = $guard->user();
                $sid = $request->session()->getId();

                // LOCK: hanya block takeover kalau user masih mengisi (ping < 3 menit)
                $LOCK_TTL_MIN = 3;
                $lockActive = $user->cl_in_progress
                    && $user->cl_last_ping
                    && now()->diffInMinutes($user->cl_last_ping) < $LOCK_TTL_MIN;

                if (!empty($user->control_session_id) && $user->control_session_id !== $sid && $lockActive) {
                    $guard->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Akun CONTROL LEADER sedang mengisi checksheet di perangkat lain.',
                    ]);
                }

                // takeover OK (kalau tidak sedang aktif)
                $user->forceFill([
                    'control_session_id' => $sid,
                    'cl_in_progress' => false,     // reset flag nyangkut
                    'cl_last_ping' => now(),
                ])->save();

                $draft = ChecksheetDraft::where('user_id', $user->id)
                    ->where('is_active', true)    // ← tanpa TTL
                    ->latest('updated_at')
                    ->first();

                if ($draft) {
                    $draft->forceFill([
                        'session_id' => $request->session()->getId(),
                        'last_ping' => now(),
                    ])->save();

                    return redirect()->route('control.checksheets.partA', [
                        'detail' => $draft->schedule_detail_id,
                        'type' => $draft->phase,
                    ]);
                }

                // kalau tidak sedang mengisi (atau lock kadaluarsa) → boleh takeover sesi lama
                $oldSid = $user->control_session_id;

                // (opsional) jika SESSION_DRIVER=database, hapus baris sesi lama
                if (config('session.driver') === 'database' && $oldSid && $oldSid !== $sid) {
                    \DB::connection(config('session.connection'))
                        ->table(config('session.table', 'sessions'))
                        ->where('id', $oldSid)->delete();
                }
            }

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
        $request->session()->forget(['active_app', 'login_app_type', 'url.intended', 'cl_in_progress']);

        if (Auth::guard('web_control_leader')->check()) {
            Auth::guard('web_control_leader')->user()
                    ?->forceFill(['control_session_id' => null, 'cl_in_progress' => false])
                ->save();
        }

        Auth::logout();
        Auth::guard('web')->logout();
        Auth::guard('web_control_leader')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Arahkan ke halaman login netral
        return redirect('/');
    }

}
