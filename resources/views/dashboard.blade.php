@extends('layouts.app')

@push('subtitle')
    {{-- Badge Nama User (Tetap Sama) --}}
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-person-badge me-2"></i>
        <span class="fw-bold text-uppercase small">{{ $userName }}</span>
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

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-xl-4 justify-content-center text-center">

                {{-- Baris 1 --}}
                <div class="col">
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

                <div class="col">
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

                <div class="col">
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
                <div class="col">
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

                <div class="col">
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

                <div class="col">
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
            <div id="supervisor-menu-container" class="my-auto">
                <div id="menu-main"
                    class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-3 g-xl-4 justify-content-center animate-fade-in"
                    style="max-width: 900px; margin: 0 auto;">
                    {{-- Grid 2x2 untuk Supervisor biar pas di tengah --}}
                    <div class="col">
                        <a href="{{ route('checksheets.create', ['type' => 'leader']) }}" class="btn-dashboard h-100">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Checksheet<br><small>Supervisor</small></span>
                        </a>
                    </div>
                    <div class="col">
                        <button type="button" class="btn-dashboard w-100 h-100"
                            onclick="window.location.href='{{ route('reports.index') }}'">
                            <i class="bi bi-file-text"></i>
                            <span>Report<br><small>Activity</small></span>
                        </button>
                    </div>
                    <div class="col">
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
                    <div class="col">
                        <button type="button" class="btn-dashboard w-100 h-100" id="btn-show-database">
                            <i class="bi bi-database-gear"></i>
                            <span>Database<br><small>& Schedule</small></span>
                        </button>
                    </div>
                </div>

                {{-- Menu Database (Hidden by default) --}}
                <div id="menu-database" class="row row-cols-1 row-cols-md-3 g-3 justify-content-center d-none">
                    <div class="col-12 text-center mb-2 d-flex align-items-end justify-content-center">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-back-menu">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                    </div>
                    <div class="col">
                        <a href="{{ route('operator.index') }}" class="btn-dashboard">
                            <i class="bi bi-people"></i><span>Data Operator</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('schedule.leader') }}" class="btn-dashboard">
                            <i class="bi bi-calendar-check"></i><span>Schedule Ops</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('schedule.index') }}" class="btn-dashboard">
                            <i class="bi bi-calendar-range"></i><span>Schedule Leader</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- ROLE: Management atau YPQ --}}
        @elseif(in_array(auth()->user()->role, ['management', 'ypq']))
            <div class="row g-4 justify-content-center animate-fade-in my-auto">
                <div id="menu-main"
                    class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-3 g-xl-4 justify-content-center animate-fade-in"
                    style="max-width: 900px; margin: 0 auto;">
                    <div class="col">
                        <a href="{{ route('question.index') }}" class="btn-dashboard">
                            <i class="bi bi-question-circle"></i>
                            <span>Question List</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('users.index') }}" class="btn-dashboard">
                            <i class="bi bi-person-gear"></i>
                            <span>Users List</span>
                        </a>
                    </div>
                    <div class="col">
                        <button type="button" class="btn-dashboard w-100 h-100"
                            onclick="window.location.href='{{ route('reports.index') }}'">
                            <i class="bi bi-file-text"></i>
                            <span>Report<br><small>Activity</small></span>
                        </button>
                    </div>
                    <div class="col">
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
        <div class="fixed-bottom bg-white border-top shadow-lg py-1 px-4 d-flex justify-content-between align-items-center"
            style="z-index: 1030;">
            <div class="d-none d-md-block">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Current
                    Session:</small>
                <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
            </div>

            <x-logout />
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

            $('#btn-show-database').on('click', function() {
                mainGrid.addClass('d-none');
                dbGrid.removeClass('d-none').hide().fadeIn(200);
            });

            $('#btn-back-menu').on('click', function() {
                dbGrid.addClass('d-none');
                mainGrid.removeClass('d-none').hide().fadeIn(200);
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
        });
    </script>
@endsection
