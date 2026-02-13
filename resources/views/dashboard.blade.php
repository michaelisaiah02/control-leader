@extends('layouts.app')

@push('subtitle')
    {{-- Badge Nama User (Tetap Sama) --}}
    <div
        class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-pill bg-white bg-opacity-10 border border-light text-white mt-1 mb-0 subtitle">
        <i class="bi bi-person-badge me-2"></i>
        <span class="fw-bold text-uppercase small">{{ $leaderName }}</span>
        <span class="mx-2">|</span>
        <span class="fw-light text-uppercase small">{{ $leaderRole }}</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid dashboard-container pb-5 pb-md-0">
        {{-- ROLE: LEADER --}}
        @if (auth()->user()->role === 'leader')
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-xl-4 justify-content-center text-center">

                {{-- Baris 1 --}}
                <div class="col">
                    <a href="{{ route('checksheets.create', ['type' => 'awal_shift']) }}" class="btn-dashboard">
                        <i class="bi bi-sunrise"></i>
                        <span>Awal Shift Sebelum
                            Bekerja<br><small class="fw-light opacity-75" style="font-size: 0.7em">Checksheet</small></span>
                    </a>
                </div>

                <div class="col">
                    <a href="{{ route('checksheets.create', ['type' => 'saat_bekerja']) }}" class="btn-dashboard">
                        <i class="bi bi-tools"></i>
                        <span>Saat Bekerja<br><small class="fw-light opacity-75"
                                style="font-size: 0.7em">Checksheet</small></span>
                    </a>
                </div>

                <div class="col">
                    <a href="{{ route('checksheets.create', ['type' => 'setelah_istirahat']) }}" class="btn-dashboard">
                        <i class="bi bi-cup-hot"></i>
                        <span>Setelah Istirahat<br><small class="fw-light opacity-75"
                                style="font-size: 0.7em">Checksheet</small></span>
                    </a>
                </div>

                {{-- Baris 2 --}}
                <div class="col">
                    <a href="{{ route('checksheets.create', ['type' => 'akhir_shift']) }}" class="btn-dashboard">
                        <i class="bi bi-sunset"></i>
                        <span>Akhir Shift Sebelum
                            Pulang<br><small class="fw-light opacity-75" style="font-size: 0.7em">Checksheet</small></span>
                    </a>
                </div>

                <div class="col">
                    <a href="{{ route('reports.index') }}" class="btn-dashboard">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Report</span>
                    </a>
                </div>

                <div class="col">
                    <a href="{{ route('listProblem.index') }}" class="btn-dashboard position-relative btn-dashboard-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>List Problem</span>
                        <span
                            class="position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill bg-danger border border-white shadow-sm">
                            99+
                        </span>
                    </a>
                </div>
            </div>

            {{-- ROLE: SUPERVISOR --}}
        @elseif(auth()->user()->role === 'supervisor')
            {{-- Gunakan logika row yang sama untuk supervisor --}}
            <div id="supervisor-menu-container">
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
                        <button type="button" class="btn-dashboard w-100 h-100">
                            <i class="bi bi-file-text"></i>
                            <span>Report<br><small>Activity</small></span>
                        </button>
                    </div>
                    <div class="col">
                        <button type="button" class="btn-dashboard w-100 h-100"
                            onclick="window.location.href='{{ route('listProblem.index') }}'">
                            <i class="bi bi-exclamation-octagon"></i>
                            <span>List Problem</span>
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
                    <div class="col-12 text-center mb-2">
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
                        <a href="{{ route('schedule.index') }}" class="btn-dashboard">
                            <i class="bi bi-calendar-check"></i><span>Schedule Ops</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('schedule.index', ['supervisor' => true]) }}" class="btn-dashboard">
                            <i class="bi bi-calendar-range"></i><span>Schedule Leader</span>
                        </a>
                    </div>
                </div>
            </div>
            {{-- ROLE: ADMIN --}}
        @elseif(in_array(auth()->user()->role, ['admin', 'management', 'ypq']))
            <div class="row g-4 justify-content-center animate-fade-in">
                <div class="col-12 col-md-6">
                    <a href="{{ route('admin.question.index') }}" class="btn-dashboard">
                        <i class="bi bi-question-circle"></i>
                        <span>Question List</span>
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <a href="{{ route('admin.users.index') }}" class="btn-dashboard">
                        <i class="bi bi-person-gear"></i>
                        <span>Users List</span>
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
        <div class="fixed-bottom bg-white border-top shadow-lg py-1 px-4 d-flex justify-content-between align-items-center"
            style="z-index: 1030;">
            <div class="d-none d-md-block">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Current
                    Session:</small>
                <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
            </div>

            <form action="{{ route('logout') }}" method="post" class="ms-auto">
                @csrf
                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold d-flex align-items-center">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </button>
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
