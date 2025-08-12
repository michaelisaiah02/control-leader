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
        // Penjelasan: Jika user mengakses /login tanpa memilih aplikasi,
        // kita kembalikan mereka ke halaman utama untuk memilih.
        if (!$request->has('app') || !in_array($request->query('app'), ['kalibrasi', 'control_leader'])) {
            return redirect()->route('welcome');
        }

        // Penjelasan: Ambil tipe aplikasi dari URL (e.g., 'kalibrasi').
        $appType = $request->query('app');

        // Penjelasan: Tentukan nama yang akan ditampilkan di halaman login.
        $appName = match ($appType) {
            'kalibrasi' => 'Kalibrasi',
            'control_leader' => 'Control Leader',
            default => 'APPLICATION'
        };

        // Penjelasan: Kirim variabel $appName dan $appType ke view.
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
        // Penjelasan: Validasi input dasar.
        $request->validate([
            'employeeID' => 'required|string',
            'password' => 'required|string',
            'app' => 'required|string|in:kalibrasi,control_leader', // Pastikan 'app' dikirim
        ]);

        $credentials = $request->only('employeeID', 'password');

        if (Auth::attempt($credentials)) {
            // Penjelasan: Jika login berhasil, regenerate session untuk keamanan.
            $request->session()->regenerate();

            // Penjelasan: INI BAGIAN KUNCI! Simpan aplikasi yang aktif ke session.
            $request->session()->put('active_app', $request->input('app'));

            return redirect()->intended('/dashboard');
        }

        // Penjelasan: Jika login gagal, kembalikan ke halaman sebelumnya dengan pesan error.
        throw ValidationException::withMessages([
            'error' => 'Employee ID atau Password salah.',
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        // Penjelasan: INI BAGIAN KUNCI! Hapus session 'active_app' saat logout.
        $request->session()->forget('active_app');

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
