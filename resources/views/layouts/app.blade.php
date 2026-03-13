<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Control Leader</title>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    @vite(['resources/js/app.js', 'resources/sass/app.scss'])
    @yield('styles')
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light text-light sticky-top
        {{ request()->is('login') ? 'bg-transparent px-3' : 'shadow-sm bg-primary px-3 px-lg-5 py-1' }}"
        id="navbar-control-leader">

        <div class="container-fluid px-0">
            {{-- Wrapper Utama: Flex Column di HP, Row di Desktop --}}
            <div class="d-flex flex-row align-items-center w-100 position-relative">

                {{-- Kiri: Logo PT --}}
                <div class="my-auto me-lg-auto">
                    <a class="navbar-brand m-0" href="/">
                        <img src="{{ asset('image/logo-pt.png') }}" alt="Logo PT" class="logo">
                    </a>
                </div>

                {{-- Tengah: Judul & Info --}}
                <div class="text-center flex-grow-1 {{ request()->is('login') ? 'text-light' : 'text-bg-primary' }}"
                    id="title-section">

                    {{-- Judul Utama --}}
                    <p id="main-title" class="main-title text-uppercase m-0">
                        Control Leader
                    </p>

                    {{-- Nama Perusahaan --}}
                    <p class="company-name m-0">PT. CATURINDO AGUNGJAYA RUBBER</p>
                    @stack('subtitle')
                </div>

                {{-- Kanan: Logo Rice & User Info --}}
                <div class="my-auto ms-lg-auto d-flex flex-column flex-lg-row align-items-center gap-3">

                    {{-- User Info Box (Hanya muncul kalau bukan login page) --}}
                    @if (!request()->is('login') && !request()->is('dashboard') && auth()->check())
                        <div class="user-badge border border-1 p-2 text-uppercase text-center rounded bg-primary-dark d-none d-lg-block"
                            style="min-width: 140px;">
                            <div class="fw-bold text-truncate" style="max-width: 150px;">
                                {{ strlen(auth()->user()->name) > 10 ? explode(' ', auth()->user()->name)[0] : auth()->user()->name }}
                            </div>
                            <small class="d-block text-white-50">
                                {{ auth()->user()->role }}
                            </small>
                            @if (!request()->is('login'))
                                <x-logout />
                            @endif
                        </div>
                    @endif

                    {{-- Logo Rice --}}
                    <a class="navbar-brand m-0" href="/">
                        <img src="{{ asset('image/logo-rice.png') }}" alt="Logo Rice" class="logo">
                    </a>
                </div>

            </div>
        </div>
    </nav>

    {{-- Content Wrapper --}}
    <main class="pt-3">
        @yield('content')
    </main>

    <!-- Modal Auto Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Auto Logout</h5>
                </div>
                <div class="modal-body">
                    No activity detected. You will be logged out automatically in a few seconds...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary"
                        onclick="localStorage.removeItem('forceLogout'); document.getElementById('auto-logout-form').submit();">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Logout -->
    <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    @yield('scripts')

    <div id="connection-indicator" style="display: none; position: fixed; bottom: 1rem; right: 1rem; z-index: 9999;">
        <div class="alert alert-danger mb-0 py-2 px-3" role="alert">
            ⚠️ Connection was lost...
        </div>
    </div>

    @auth
        <script type="module">
            /**
             * MODULE: Auto Logout & Connection Checker
             * Refactored for modularity and readability.
             */

            // --- Config ---
            const CONFIG = {
                maxIdleTime: 5 * 60 * 1000, // 5 Menit
                warningTime: 10 * 1000, // 10 Detik sebelum logout di modal
                pingUrl: "{{ route('ping') }}",
                pingInterval: 10000
            };

            // --- Auto Logout System ---
            let idleTimeout, idleInterval;
            let lastActiveTime = Date.now();
            const logoutModalEl = document.getElementById('logoutModal');
            const logoutForm = document.getElementById('auto-logout-form');
            let logoutModalInstance;

            function resetIdleTimer() {
                if (localStorage.getItem('forceLogout') === 'true') return;
                lastActiveTime = Date.now();
                clearTimeout(idleTimeout);
                clearInterval(idleInterval); // Stop counting logic if running

                // Start waiting again
                idleTimeout = setTimeout(checkIdleStatus, 1000);
            }

            function checkIdleStatus() {
                idleInterval = setInterval(() => {
                    const now = Date.now();
                    const idleDuration = now - lastActiveTime;

                    if (idleDuration >= CONFIG.maxIdleTime) {
                        triggerLogoutSequence();
                    }
                }, 1000);
            }

            function triggerLogoutSequence() {
                clearInterval(idleInterval);
                clearTimeout(idleTimeout);
                localStorage.setItem('forceLogout', 'true');

                if (!logoutModalInstance) {
                    logoutModalInstance = new bootstrap.Modal(logoutModalEl);
                }
                logoutModalInstance.show();

                // Final countdown inside modal
                setTimeout(() => {
                    if (logoutModalEl.classList.contains('show')) {
                        performLogout();
                    }
                }, CONFIG.warningTime);
            }

            window.forceLogoutNow = function() {
                performLogout(); // Function globally accessible for button onclick
            }

            function performLogout() {
                localStorage.removeItem('forceLogout');
                logoutForm.submit();
            }

            // --- Connection Checker System ---
            const connIndicator = document.getElementById('connection-indicator');
            let isOffline = false;

            async function checkConnection() {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 5000);

                    const response = await fetch(CONFIG.pingUrl, {
                        method: 'GET',
                        signal: controller.signal,
                        cache: 'no-store',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    clearTimeout(timeoutId);

                    if (!response.ok) throw new Error('Server Error');

                    if (isOffline) {
                        connIndicator.classList.remove('show'); // Bootstrap toast hide
                        isOffline = false;
                    }
                } catch (error) {
                    if (!isOffline) {
                        connIndicator.classList.add('show'); // Bootstrap toast show
                        isOffline = true;
                    }
                }
            }

            // --- Initialization ---
            document.addEventListener('DOMContentLoaded', () => {
                // 1. Idle Listeners
                ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt => {
                    document.addEventListener(evt, resetIdleTimer, {
                        passive: true
                    });
                });

                // 2. Storage Sync (Tab lain logout, ini ikut logout)
                window.addEventListener('storage', (event) => {
                    if (event.key === 'forceLogout' && event.newValue === 'true') {
                        triggerLogoutSequence();
                    }
                });

                // 3. Start Systems
                resetIdleTimer();
                setInterval(checkConnection, CONFIG.pingInterval);
            });
        </script>
    @endauth
</body>

</html>
