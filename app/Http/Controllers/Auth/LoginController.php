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

                // grace window berapa menit sejak heartbeat terakhir dianggap "aktif"
                $LOCK_TTL_MIN = 3;

                $lockActive = $user->cl_in_progress
                    && $user->cl_last_ping
                    && now()->diffInMinutes($user->cl_last_ping) < $LOCK_TTL_MIN;

                if (!empty($user->control_session_id) && $user->control_session_id !== $sid && $lockActive) {
                    // masih aktif mengisi → tolak login baru
                    $guard->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    throw ValidationException::withMessages([
                        'error' => 'Akun CONTROL LEADER sedang mengisi checksheet di perangkat lain.',
                    ]);
                }

                // kalau tidak sedang mengisi (atau lock kadaluarsa) → boleh takeover sesi lama
                $oldSid = $user->control_session_id;

                $user->forceFill([
                    'control_session_id' => $sid,
                    // reset flag biar gak "nyangkut"
                    'cl_in_progress' => false,
                    'cl_last_ping' => now(),
                ])->save();

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
