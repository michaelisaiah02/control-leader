@extends('layouts.app')

@push('subtitle')
    <button id="title" class="btn btn-lg btn-outline-light fw-medium p-0 my-auto sub-judul" disabled>
        {{ $leaderName }} - {{ $leaderRole }}
    </button>
@endpush

@section('content')
    <div class="container text-center mt-4">
        {{-- Baris Pertama Tombol --}}
        <div class="row justify-content-center g-5 mb-4">
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">AWAL
                    SHIFT SEBELUM
                    BEKERJA</a>
            </div>
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SAAT
                    BEKERJA</a>
            </div>
        </div>

        {{-- Baris Kedua Tombol --}}
        <div class="row justify-content-center g-5 mb-4">
            <div class="col-12 col-md-4">
                <a href="#"
                    class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">SETELAH
                    ISTIRAHAT</a>
            </div>
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center rounded-4">AKHIR
                    SHIFT
                    SEBELUM PULANG</a>
            </div>
        </div>

        {{-- Tombol Report --}}
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-3 align-content-center">REPORT</a>
            </div>
        </div>
    </div>

    {{-- Tombol Logout di Kanan Bawah --}}
    <div class="position-fixed bottom-0 end-0 p-3">
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="btn btn-primary fs-5 fw-medium">Logout</button>
        </form>
    </div>
@endsection
