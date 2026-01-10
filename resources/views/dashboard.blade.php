@extends('layouts.app')

@section('title', 'Control Leader')

@push('subtitle')
    <button id="title" class="btn btn-lg btn-outline-light fw-medium p-0 my-auto sub-judul text-uppercase" disabled>
        {{ $leaderName }} - {{ $leaderRole }}
    </button>
@endpush

@section('content')
    @switch (auth()->user()->role)
        @case('leader')
            <div class="container text-center mt-4">
                {{-- Baris Pertama Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('checksheets.create', ['type' => 'awal_shift']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">AWAL
                            SHIFT SEBELUM
                            BEKERJA</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="{{ route('checksheets.create', ['type' => 'saat_bekerja']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SAAT
                            BEKERJA</a>
                    </div>
                </div>

                {{-- Baris Kedua Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('checksheets.create', ['type' => 'setelah_istirahat']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SETELAH
                            ISTIRAHAT</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="{{ route('checksheets.create', ['type' => 'akhir_shift']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">AKHIR
                            SHIFT
                            SEBELUM PULANG</a>
                    </div>
                </div>

                {{-- Tombol Report --}}
                <div class="row justify-content-center g-5">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('reports.index') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 rounded-4 align-content-center report">REPORT</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="{{ route('listProblem.index') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative">
                            LIST PROBLEM
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                99+
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        @break

        @case('supervisor')
            <div class="container text-center mt-4">
                {{-- Baris Pertama Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-5 menu1">
                        <a href="{{ route('checksheets.create', ['type' => 'leader']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">CHECKSHEET
                            SUPERVISOR</a>
                    </div>
                    <div class="col-12 col-md-5 menu1">
                        <button
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4 report">REPORT</button>
                    </div>
                    {{-- Menu Database & Schedule --}}
                    <div class="col-12 col-md-5 d-none menu2">
                        <a href="{{ route('operator.index') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">DATA OPERATOR</a>
                    </div>
                </div>

                {{-- Baris Kedua Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-5 menu1">
                        <button
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4 position-relative"
                            id="problem-btn">
                            LIST PROBLEM
                            <a href="#"
                                class="position-absolute top-0 start-100 translate-middle bg-danger border border-light rounded-pill fs-6 text-decoration-none text-primary px-2">
                                99+
                                <span class="visually-hidden">Dangers</span>
                            </a>
                        </button>
                    </div>
                    <div class="col-12 col-md-5 menu1">
                        <button class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4"
                            id="data-btn">DATABASE
                            OPERATOR &
                            SCHEDULE CONTROL</button>
                    </div>

                    {{-- Menu Database & Schedule --}}
                    <div class="col-12 col-md-5 d-none menu2">
                        <a href="{{ route('schedule.index') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SCHEDULE CONTROL
                            OPERATOR</a>
                    </div>
                    <div class="col-12 col-md-5 d-none menu2">
                        <a href="{{ route('schedule.index', ['supervisor' => true]) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SCHEDULE CONTROL
                            LEADER</a>
                    </div>
                </div>
            </div>

            @section('scripts')
                <script type="module">
                    $(function() {
                        const toggleMenus = () => {
                            $('.menu1, .menu2, #back-btn').toggleClass('d-none');
                        };

                        $('#data-btn, #back-btn').on('click', toggleMenus);

                        $('#problem-btn').on('click', function() {
                            window.location.href = "";
                        });
                    });
                </script>
            @endsection
        @break

        @case('admin')
            <div class="container text-center mt-4">
                {{-- Baris Pertama Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('admin.question.index') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">QUESTION LIST</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="{{ route('admin.users.index') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">USERS
                            LIST</a>
                    </div>
                </div>

                {{-- Baris Kedua Tombol --}}
                {{-- <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="#" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">USERS
                            LIST</a>
                    </div>
                </div> --}}
            </div>
        @break

        @default
            <p class="fs-1 position-absolute top-50 start-50 translate-middle text-dark">
                PERMISSION
                DENIED</p>
    @endswitch
    {{-- Tombol Logout di Kanan Bawah --}}
    <div class="position-fixed bottom-0 end-0 p-3">
        <div class="d-flex gap-2">
            <button class="btn btn-primary fs-5 d-none" id="back-btn">Back</button>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-primary fs-5 fw-medium">Logout</button>
            </form>
        </div>
    </div>

    <x-toast />
@endsection
