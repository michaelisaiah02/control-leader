@extends('layouts.app')

@push('subtitle')
    <button id="title" class="btn btn-lg btn-outline-light fw-medium p-0 my-auto sub-judul" disabled>
        {{ $leaderName }} - {{ $leaderRole }}
    </button>
@endpush

@section('content')
    <div class="container text-center mt-4">
        {{-- Menampilkan Nama Leader dan Bagian --}}
        <div class="mb-4">
            <div class="d-inline-block border border-2 border-primary text-primary p-2 px-4 rounded-pill">
                <h5 class="m-0 fw-bold">{{ $leaderName }} - {{ $leaderRole }}</h5>
            </div>
        </div>

        {{-- Baris Pertama Tombol --}}
        <div class="row justify-content-center g-3 mb-3">
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 py-3 fw-bold">AWAL SHIFT SEBELUM BEKERJA</a>
            </div>
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 py-3 fw-bold">SAAT BEKERJA</a>
            </div>
        </div>

        {{-- Baris Kedua Tombol --}}
        <div class="row justify-content-center g-3 mb-5">
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 py-3 fw-bold">SETELAH ISTIRAHAT</a>
            </div>
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-primary btn-lg w-100 py-3 fw-bold">AKHIR SHIFT SEBELUM PULANG</a>
            </div>
        </div>

        {{-- Tombol Report --}}
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-4">
                <a href="#" class="btn btn-dark btn-lg w-100 py-3 fw-bold">REPORT</a>
            </div>
        </div>
    </div>

    {{-- Tombol Logout di Kanan Bawah --}}
    <div class="position-fixed bottom-0 end-0 p-3">
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="btn btn-secondary">Logout</button>
        </form>
    </div>
@endsection
