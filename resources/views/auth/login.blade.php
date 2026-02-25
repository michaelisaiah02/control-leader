@extends('layouts.app')

@section('styles')
    <style>
        /* Modern Background Setup */
        body {
            /* Background image fix biar cover seluruh layar */
            background: url("{{ asset('image/bg.jpeg') }}") no-repeat center center fixed;
            background-size: cover;
            /* Penting: matikan overflow biar gak ada scrollbar gak jelas */
            overflow: hidden;
        }

        /* FIX LAYOUT: Override Navbar Layout Utama khusus page ini.
                       Kita bikin navbar melayang di atas background, jadi gak nambah tinggi halaman.
                    */
        nav.navbar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            background: transparent !important;
            /* Paksa transparan */
            box-shadow: none !important;
            /* Hapus shadow bawaan navbar kalo ada */
        }

        /* Glassmorphism Card (Tetap sama) */
        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        /* Form Controls (Tetap sama) */
        .btn-shift-check:checked+.btn-shift {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
            box-shadow: 0 0 15px rgba(var(--bs-primary-rgb), 0.6);
            font-weight: 800;
        }

        .btn-shift {
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            transition: all 0.2s;
        }

        .btn-shift:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Fix input visibility on glass */
        .form-floating>.form-control {
            background: rgba(255, 255, 255, 0.9);
            border: none;
        }

        .form-floating>.form-control:focus {
            background: #fff;
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.5);
        }
    </style>
@endsection

@section('content')
    {{-- Wrapper Full Height & Center Alignment --}}
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card login-card rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('login') }}" method="POST" id="loginForm">
                        @csrf

                        {{-- Employee ID --}}
                        <div class="form-floating mb-3">
                            {{-- UX Hack: inputmode="numeric" biar keyboard HP angka yang muncul --}}
                            <input type="text" class="form-control" placeholder="Employee ID" id="employeeID"
                                name="employeeID" value="{{ old('employeeID') }}" inputmode="numeric" required autofocus>
                            <label for="employeeID" class="text-secondary">Employee ID</label>
                        </div>

                        {{-- Password with Toggle --}}
                        <div class="input-group mb-4">
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" placeholder="Password" id="password"
                                    name="password" required autocomplete="current-password"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <label for="password" class="text-secondary">Password</label>
                            </div>
                            <button class="btn btn-light border-0" type="button" id="togglePassword"
                                style="border-top-right-radius: 0.375rem; border-bottom-right-radius: 0.375rem;">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>

                        {{-- Shift Selector (Segmented Control) --}}
                        <div class="mb-4">
                            <label class="d-block text-white text-center mb-2 small text-uppercase letter-spacing-2">Select
                                Shift</label>
                            <div class="d-flex gap-2">
                                @for ($i = 1; $i <= 3; $i++)
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check btn-shift-check" name="shift"
                                            id="shift{{ $i }}" value="{{ $i }}"
                                            {{ $i == 1 ? 'checked' : '' }}>
                                        <label class="btn btn-shift w-100 py-2 rounded-3" for="shift{{ $i }}">
                                            {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-lg" id="btnLogin">
                                Login
                            </button>
                        </div>

                        <div class="mt-4 text-center">
                            <small class="text-white-50 fst-italic">
                                Jika gagal login, hubungi IT
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // 1. Password Toggle
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#password');
                const icon = $('#toggleIcon');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                }
            });

            // 2. Loading State on Submit
            $('#loginForm').on('submit', function() {
                const btn = $('#btnLogin');
                btn.prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Authenticating...
                `);
            });

            // 3. Idle Timer (Optimized)
            // Cukup reload kalau diem 15 menit, biar form bersih lagi.
            const MAX_IDLE_TIME = 15 * 60 * 1000;
            let idleTimeout;

            function resetTimer() {
                clearTimeout(idleTimeout);
                idleTimeout = setTimeout(() => {
                    location.reload();
                }, MAX_IDLE_TIME);
            }

            // Listeners
            $(document).on('mousemove keydown scroll touchstart', resetTimer);
            resetTimer(); // Start initial
        });
    </script>
@endsection
