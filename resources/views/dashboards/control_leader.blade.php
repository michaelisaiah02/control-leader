@extends('layouts.app')

@push('subtitle')
    <button id="title" class="btn btn-lg btn-outline-light fw-medium p-0 my-auto sub-judul text-uppercase" disabled>
        {{ $leaderName }} - {{ $leaderRole }}
    </button>
@endpush

@section('content')
    @switch (auth()->guard('web_control_leader')->user()->role)
        @case('leader')
            <div class="container text-center mt-4">
                {{-- Baris Pertama Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('control.checksheets.create', ['type' => 'awal_shift']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">AWAL
                            SHIFT SEBELUM
                            BEKERJA</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="{{ route('control.checksheets.create', ['type' => 'saat_bekerja']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SAAT
                            BEKERJA</a>
                    </div>
                </div>

                {{-- Baris Kedua Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('control.checksheets.create', ['type' => 'setelah_istirahat']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SETELAH
                            ISTIRAHAT</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="{{ route('control.checksheets.create', ['type' => 'akhir_shift']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">AKHIR
                            SHIFT
                            SEBELUM PULANG</a>
                    </div>
                </div>

                {{-- Tombol Report --}}
                <div class="row justify-content-center g-5">
                    <div class="col-12 col-md-4">
                        <button class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center report">REPORT</button>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="#"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">PROBLEM
                            CONSISTENCY</a>
                    </div>
                </div>
            </div>
        @break

        @case('supervisor')
            <div class="container text-center mt-4">
                {{-- Baris Pertama Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('control.checksheets.create', ['type' => 'leader']) }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">CHECKSHEET
                            SUPERVISOR</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <button
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4 report">REPORT</button>
                    </div>
                </div>

                {{-- Baris Kedua Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="#"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">PROBLEM CONTROL
                            MEMBER</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="#"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">PROBLEM CONTROL
                            LEADER</a>
                    </div>
                </div>
            </div>
        @break

        @case('admin')
            <div class="container text-center mt-4">
                {{-- Baris Pertama Tombol --}}
                <div class="row justify-content-center g-5 mb-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('control.question.index') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">QUESTION LIST</a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="#" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">USERS
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
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="btn btn-primary fs-5 fw-medium">Logout</button>
        </form>
    </div>

    <x-toast />
@endsection
