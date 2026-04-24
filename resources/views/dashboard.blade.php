@extends('layouts.app')

@push('subtitle')
    {{-- Badge Nama User (Tetap Sama) --}}
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-person-badge me-2"></i>
        <span class="fw-bold text-uppercase small text-truncate">{{ $userName }}</span>
        <span class="mx-2">|</span>
        <span class="fw-light text-uppercase small">{{ $userRole }}</span>
    </div>
@endpush

@section('styles')
    <style>
        /* ========================================= */
        /* OPTIMASI DASHBOARD CARDS */
        /* ========================================= */
        .btn-dashboard {
            text-decoration: none !important;
            /* Ilangin garis bawah norak */
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }

        /* Efek ngangkat & shadow pas di-hover */
        .btn-dashboard:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15) !important;
        }

        /* Styling Badge Jam Default (Terang) */
        .time-badge {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            /* Biar icon & text bener-bener sejajar */
            align-items: center;
            /* Rata tengah vertikal */
            justify-content: center;
            gap: 6px;
            /* Jarak icon dan text */
        }

        /* 🔥 INI OBATNYA: Maksa ikon jam balik normal 🔥 */
        .time-badge i {
            font-size: 1rem !important;
            /* Ukuran standar, batalin ukuran raksasa dari btn-dashboard */
            line-height: 1;
            margin: 0 !important;
            /* Bersihin margin kalau ada */
        }

        /* Styling Badge Jam pas di-hover (Background Gelap) */
        .btn-dashboard:hover .time-badge {
            background-color: rgba(255, 255, 255, 0.15);
            /* Transparan putih */
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.3);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid dashboard-container pb-5 pb-md-0">
        {{-- ROLE: LEADER --}}
        @if (auth()->user()->role === 'leader')
            @php
                $currentShift = session('shift', 1);

                $timeRanges = [
                    1 => [
                        'awal_shift' => '07:00 - 08:30',
                        'saat_bekerja' => '08:30 - 12:00',
                        'setelah_istirahat' => '13:00 - 14:00',
                        'akhir_shift' => '14:00 - 15:00',
                    ],
                    2 => [
                        'awal_shift' => '15:00 - 16:30',
                        'saat_bekerja' => '20:00 - 22:00',
                        'setelah_istirahat' => '19:00 - 20:00',
                        'akhir_shift' => '22:00 - 23:00',
                    ],
                    3 => [
                        'awal_shift' => '23:00 - 00:30',
                        'saat_bekerja' => '00:30 - 04:00',
                        'setelah_istirahat' => '05:00 - 06:00',
                        'akhir_shift' => '06:00 - 07:00',
                    ],
                ];

                $ranges = $timeRanges[$currentShift] ?? $timeRanges[1];
            @endphp

            <div
                class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-xl-4 justify-content-center text-center my-auto pb-5 pb-md-0">

                {{-- Baris 1 --}}
                <div class="col my-2">
                    <a href="{{ route('checksheets.create', ['type' => 'awal_shift']) }}"
                        class="btn-dashboard d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-sunrise mb-0 pb-0"></i>
                        <span>
                            Awal Shift Sebelum Bekerja
                            <br>
                            <small class="fw-light opacity-75" style="font-size: 0.7em">Checksheet</small>
                        </span>
                        <div class="mt-3 time-badge rounded-pill px-3 py-1">
                            <i class="bi bi-clock me-1"></i> {{ $ranges['awal_shift'] }}
                        </div>
                    </a>
                </div>

                <div class="col my-2">
                    <a href="{{ route('checksheets.create', ['type' => 'saat_bekerja']) }}"
                        class="btn-dashboard d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-tools mb-0 pb-0"></i>
                        <span>
                            Saat Bekerja
                            <br>
                            <small class="fw-light opacity-75" style="font-size: 0.7em">Checksheet</small>
                        </span>
                        <div class="mt-3 time-badge rounded-pill px-3 py-1">
                            <i class="bi bi-clock me-1"></i> {{ $ranges['saat_bekerja'] }}
                        </div>
                    </a>
                </div>

                <div class="col my-2">
                    <a href="{{ route('checksheets.create', ['type' => 'setelah_istirahat']) }}"
                        class="btn-dashboard d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-cup-hot mb-0 pb-0"></i>
                        <span>
                            Setelah Istirahat
                            <br>
                            <small class="fw-light opacity-75" style="font-size: 0.7em">Checksheet</small>
                        </span>
                        <div class="mt-3 time-badge rounded-pill px-3 py-1">
                            <i class="bi bi-clock me-1"></i> {{ $ranges['setelah_istirahat'] }}
                        </div>
                    </a>
                </div>

                {{-- Baris 2 --}}
                <div class="col my-2">
                    <a href="{{ route('checksheets.create', ['type' => 'akhir_shift']) }}"
                        class="btn-dashboard d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-sunset mb-0 pb-0"></i>
                        <span>
                            Akhir Shift Sebelum Pulang
                            <br>
                            <small class="fw-light opacity-75" style="font-size: 0.7em">Checksheet</small>
                        </span>
                        <div class="mt-3 time-badge rounded-pill px-3 py-1">
                            <i class="bi bi-clock me-1"></i> {{ $ranges['akhir_shift'] }}
                        </div>
                    </a>
                </div>

                <div class="col my-2">
                    <a href="{{ route('reports.index') }}"
                        class="btn-dashboard d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-file-earmark-bar-graph mb-0 pb-0"></i>
                        <span>
                            Report
                            <br>
                            <small class="fw-light opacity-0" style="font-size: 0.7em">.</small> {{-- Spacer biar tingginya rata --}}
                        </span>
                    </a>
                </div>

                <div class="col my-2">
                    <a href="{{ route('listProblem.index') }}"
                        class="btn-dashboard position-relative btn-dashboard-danger d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-exclamation-triangle mb-0 pb-0"></i>
                        <span>
                            List Problem
                            <br>
                            <small class="fw-light opacity-0" style="font-size: 0.7em">.</small>
                        </span>
                        @if ($problemCount > 0)
                            <span
                                class="position-absolute top-0 end-0 mt-3 me-3 badge rounded-pill bg-danger border border-white shadow-sm fs-6">
                                {{ $problemCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- ROLE: SUPERVISOR --}}
        @elseif(auth()->user()->role === 'supervisor')
            {{-- Gunakan logika row yang sama untuk supervisor --}}
            <div id="supervisor-menu-container" class="my-auto pb-5 pb-md-0">
                <div id="menu-main"
                    class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-3 g-xl-4 justify-content-center animate-fade-in">
                    {{-- Grid 2x2 untuk Supervisor biar pas di tengah --}}
                    <div class="col my-2">
                        <a href="{{ route('checksheets.create', ['type' => 'leader']) }}" class="btn-dashboard h-100">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Checksheet<br><small>Supervisor</small></span>
                        </a>
                    </div>
                    <div class="col my-2">
                        <button type="button" class="btn-dashboard w-100 h-100"
                            onclick="window.location.href='{{ route('reports.index') }}'">
                            <i class="bi bi-file-text"></i>
                            <span>Report<br><small>Activity</small></span>
                        </button>
                    </div>
                    <div class="col my-2">
                        <button type="button" class="btn-dashboard w-100 h-100 position-relative btn-dashboard-danger"
                            onclick="window.location.href='{{ route('listProblem.index') }}'">
                            <i class="bi bi-exclamation-octagon"></i>
                            <span>List Problem</span>
                            @if ($problemCount > 0)
                                <span
                                    class="position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill bg-danger border border-white shadow-sm">
                                    {{ $problemCount }}
                                </span>
                            @endif
                        </button>
                    </div>
                    <div class="col my-2">
                        <button type="button" class="btn-dashboard w-100 h-100" id="btn-show-database">
                            <i class="bi bi-database-gear"></i>
                            <span>Database<br><small>& Schedule</small></span>
                        </button>
                    </div>
                </div>

                {{-- Menu Database (Hidden by default) --}}
                <div id="menu-database" class="row g-3 d-none">
                    <!-- KOLOM MENU -->

                    <div class="col my-2">
                        <a href="{{ route('operator.index') }}" class="btn-dashboard">
                            <i class="bi bi-people"></i><span>Data Operator</span>
                        </a>
                    </div>

                    <div class="col my-2">
                        <a href="{{ route('schedule.leader') }}" class="btn-dashboard">
                            <i class="bi bi-calendar-check"></i><span>Schedule Ops</span>
                        </a>
                    </div>

                    <div class="col my-2">
                        <a href="{{ route('schedule.index') }}" class="btn-dashboard">
                            <i class="bi bi-calendar-range"></i><span>Schedule Leader</span>
                        </a>
                    </div>

                </div>

                {{-- ROLE: Management atau YPQ --}}
            @elseif(in_array(auth()->user()->role, ['management', 'ypq']))
                <div
                    class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-xl-4 justify-content-center text-center my-auto pb-5 pb-md-0">
                    <div class="col my-2">
                        <a href="{{ route('question.index') }}"
                            class="btn-dashboard d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-question-circle"></i>
                            <span>Question List</span>
                        </a>
                    </div>
                    <div class="col my-2">
                        <a href="{{ route('users.index') }}"
                            class="btn-dashboard d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-person-gear"></i>
                            <span>Users List</span>
                        </a>
                    </div>
                    <div class="col my-2">
                        <a class="btn-dashboard d-flex flex-column align-items-center justify-content-center"
                            href="{{ route('reports.index') }}">
                            <i class="bi bi-file-text"></i>
                            <span>Report<br><small>Activity</small></span>
                        </a>
                    </div>
                    <div class="col my-2">
                        <a class="btn-dashboard position-relative btn-dashboard-danger d-flex flex-column align-items-center justify-content-center"
                            href="{{ route('listProblem.index') }}">
                            <i class="bi bi-exclamation-octagon"></i>
                            <span>List Problem</span>
                            @if ($problemCount > 0)
                                <span
                                    class="position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill bg-danger border border-white shadow-sm">
                                    {{ $problemCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                    <div class="col my-2">
                        <a class="btn-dashboard d-flex flex-column align-items-center justify-content-center"
                            href="{{ route('targets.index') }}">
                            <i class="bi bi-bullseye"></i>
                            <span>Target</span>
                        </a>
                    </div>
                </div>


                {{-- DEFAULT: NO ACCESS --}}
            @else
                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 60vh;">
                    <i class="bi bi-shield-lock display-1 text-secondary mb-3"></i>
                    <h2 class="text-danger fw-bold">PERMISSION DENIED</h2>
                    <p class="text-muted">You do not have access to this page.</p>
                </div>
        @endif


        {{-- Footer Logout (Sticky Bottom) --}}
        <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center"
            style="z-index: 1030;">
            <!-- KOLOM BACK -->
            <div class="d-none me-3" id="btn-database">
                <button class="btn btn-outline-secondary rounded-pill px-4 me-2 fw-bold" id="btn-back-menu">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
            </div>
            <div class="d-none d-md-block lh-sm">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 1px;">Current
                    Session:</small>
                <div class="fw-bold text-dark" style="font-size: 0.9rem">{{ auth()->user()->name }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-primary ms-md-3" data-bs-toggle="modal"
                data-bs-target="#updatePasswordModal">
                Ganti Password
            </button>
            <x-logout />
        </div>

    </div>
    <!-- Modal Update Password -->
    <div class="modal fade" id="updatePasswordModal" tabindex="-1" aria-labelledby="updatePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="passwordForm" action="{{ route('updatePassword') }}" method="post"
                novalidate>
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="updatePasswordModalLabel">Ganti Password</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="input-group input-group-sm has-validation">
                                <input type="password" id="current-password" name="current_password"
                                    class="form-control form-control-sm" placeholder="Password Lama" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="input-group input-group-sm has-validation">
                                <input type="password" class="form-control" id="new-password" name="new_password"
                                    minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$"
                                    placeholder="Password Baru" autocomplete="new-password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Min 8 chars, Uppercase, Lowercase, Number, Symbol.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <button type="button" class="btn btn-sm btn-primary me-auto"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="btn-save"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-flex align-items-center">
                            <i class="bi bi-save me-2"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    {{-- Script sama seperti sebelumnya (Supervisor Toggle & Loading State) --}}
    <script type="module">
        $(document).ready(function() {
            const mainGrid = $('#menu-main');
            const dbGrid = $('#menu-database');
            const btnDb = $('#btn-database');

            $('#btn-show-database').on('click', function() {
                mainGrid.addClass('d-none');
                dbGrid.removeClass('d-none').hide().fadeIn(200);
                btnDb.removeClass('d-none').hide().fadeIn(200);
            });

            $('#btn-back-menu').on('click', function() {
                dbGrid.addClass('d-none');
                mainGrid.removeClass('d-none').hide().fadeIn(200);
                btnDb.addClass('d-none');
            });

            // Efek Loading
            $('.btn-dashboard').on('click', function(e) {
                if ($(this).attr('type') === 'button' && !$(this).attr('onclick')) return;
                const btn = $(this);
                btn.addClass('disabled').css('opacity', '0.8');
                btn.find('i').replaceWith(
                    '<span class="spinner-border spinner-border-sm mb-2" role="status" aria-hidden="true" style="width: 2rem; height: 2rem;"></span>'
                );
            });

            // Toggle Password
            $('#toggleCurrentPassword').on('click', function() {
                const $input = $('#current-password');
                const $icon = $(this).find('i');
                const type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
                $icon.toggleClass('bi-eye bi-eye-slash');
            });

            $('#toggleNewPassword').on('click', function() {
                const $input = $('#new-password');
                const $icon = $(this).find('i');
                const type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
                $icon.toggleClass('bi-eye bi-eye-slash');
            });

            $('#passwordForm').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    $('#btn-save').prop('disabled', true).text('Saving...');
                }
                $(this).addClass('was-validated');
            });
        });
    </script>
@endsection
