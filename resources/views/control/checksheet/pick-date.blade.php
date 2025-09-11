@extends('layouts.app')

@push('subtitle')
    <p class="fs-5 m-0">Pilih Tanggal Checksheet</p>
@endpush

@section('content')
    <div class="px-4">
        <div class="alert alert-info">Tidak ada jadwal untuk <b>hari ini</b>. Pilih salah satu tanggal yang tersedia di bawah
            ini.</div>

        @if ($allowedDates->isEmpty())
            <div class="alert alert-warning">Belum ada jadwal yang dibuat oleh Anda sebagai scheduler.</div>
        @else
            <form method="GET" action="{{ route('control.checksheets.create') }}" class="row g-3">
                <input type="hidden" name="type" value="{{ $slot }}">
                <div class="col-md-6">
                    <label class="form-label">Tanggal</label>
                    <select name="date" class="form-select" required>
                        @foreach ($allowedDates as $d)
                            <option value="{{ $d }}">{{ \Carbon\Carbon::parse($d)->translatedFormat('d F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">Lanjut</button>
                </div>
            </form>
        @endif
    </div>
@endsection
