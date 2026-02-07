@extends('layouts.app')

@push('subtitle')
    {{-- Styling subtitle disamakan dengan dashboard biar konsisten --}}
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-2 rounded-pill bg-white bg-opacity-10 border border-light text-white mt-1 mb-0 subtitle">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <span class="fw-bold text-uppercase">List Problem</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid dashboard-container">

        {{-- Wrapper Grid --}}
        {{-- Kita pake logic grid yang sama: 1 kolom di HP, 2 kolom di Tablet+ --}}
        <div class="row g-4 justify-content-center text-center">

            {{-- SECTION 1: LEADER PROBLEMS (Muncul untuk SEMUA role) --}}
            <div class="col-12 col-md-6 col-xl-5">
                <a href="{{ route('listProblem.list', 'leader-performance') }}" class="btn-dashboard position-relative">
                    <i class="bi bi-speedometer2 text-danger"></i>
                    <span>Leader<br><small class="text-danger">Performance Problem</small></span>

                    {{-- Badge Notification --}}
                    <span
                        class="position-absolute top-0 end-0 mt-3 me-3 badge rounded-pill bg-danger border border-white shadow-sm">
                        99+
                        <span class="visually-hidden">unread messages</span>
                    </span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-xl-5">
                <a href="{{ route('listProblem.list', 'leader-consistency') }}" class="btn-dashboard position-relative">
                    <i class="bi bi-calendar-x text-warning"></i>
                    <span>Leader<br><small class="text-warning">Consistency Problem</small></span>

                    <span
                        class="position-absolute top-0 end-0 mt-3 me-3 badge rounded-pill bg-danger border border-white shadow-sm">
                        5
                    </span>
                </a>
            </div>

            {{-- SECTION 2: SUPERVISOR PROBLEMS (Hanya muncul jika BUKAN Leader) --}}
            @if (auth()->user()->role !== 'leader')
                {{-- Divider Visual biar jelas pemisahnya --}}
                <div class="col-12 my-0">
                    <hr class="border-secondary opacity-25">
                </div>

                <div class="col-12 col-md-6 col-xl-5 mt-0">
                    <a href="{{ route('listProblem.list', 'supervisor-performance') }}"
                        class="btn-dashboard position-relative">
                        <i class="bi bi-graph-down-arrow text-danger"></i>
                        <span>Supervisor<br><small class="text-danger">Performance Problem</small></span>

                        <span
                            class="position-absolute top-0 end-0 mt-3 me-3 badge rounded-pill bg-danger border border-white shadow-sm">
                            12
                        </span>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-xl-5 mt-0">
                    <a href="{{ route('listProblem.list', 'supervisor-consistency') }}"
                        class="btn-dashboard position-relative">
                        <i class="bi bi-clipboard-x text-warning"></i>
                        <span>Supervisor<br><small class="text-warning">Consistency Problem</small></span>

                        <span
                            class="position-absolute top-0 end-0 mt-3 me-3 badge rounded-pill bg-danger border border-white shadow-sm">
                            3
                        </span>
                    </a>
                </div>
            @endif

        </div>
    </div>

    {{-- Sticky Back Button --}}
    <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-center" style="z-index: 1030;">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary px-5 rounded-pill fw-bold">
            <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>

    <x-toast />
@endsection
