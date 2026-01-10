@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border-1 border-white rounded-2 text-uppercase">
        Pilih Tanggal Checksheet
    </p>
@endpush

@section('content')
    <div class="px-5">
        @if (session('info'))
            <div class="alert alert-warning">{{ session('info') }}</div>
        @endif

        @if ($dates->isEmpty())
            <div class="alert alert-danger">Belum ada jadwal untuk akun ini.</div>
        @else
            <div class="mb-3">Silakan pilih tanggal yang tersedia:</div>
            <div class="row row-cols-1 row-cols-md-3 g-3">
                @foreach ($dates as $d)
                    <div class="col">
                        <a class="btn btn-outline-primary w-100 py-3"
                            href="{{ route('control.checksheets.create', ['type' => $type, 'date' => $d]) }}">
                            {{ \Illuminate\Support\Carbon::parse($d)->translatedFormat('d M Y') }}
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
