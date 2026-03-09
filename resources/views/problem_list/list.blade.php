@extends('layouts.app')

@php
    $now = \Carbon\Carbon::now();
    // Refactor Note: Logic title dipindah ke sini biar HTML bersih (Separation of Concerns)
    $titles = [
        'leader-performance' => 'Leader Performance',
        'leader-consistency' => 'Leader Consistency',
        'supervisor-performance' => 'Supervisor Performance',
        'supervisor-consistency' => 'Supervisor Consistency',
    ];
    // Ambil title, default ke 'Unknown' kalo type ga valid, terus Title Case
    $pageTitle = $titles[$type] ?? 'Unknown Category';

    // Tentukan warna badge berdasarkan tipe
    $badgeColor = str_contains($type, 'performance') ? 'bg-danger' : 'bg-warning text-dark';
    $icon = str_contains($type, 'performance') ? 'bi-speedometer2' : 'bi-calendar-check';
@endphp

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <div class="badge {{ $badgeColor }} me-2 p-2 rounded-circle">
            <i class="bi {{ $icon }}"></i>
        </div>
        <div class="d-flex flex-column text-start">
            <span class="fw-bold text-uppercase">{{ $pageTitle }}</span>
        </div>
    </div>
@endpush

@section('content')
    <div class="container-fluid pb-5">

        {{-- FILTER & TOOLBAR SECTION --}}
        <div class="card border-0 shadow-sm mb-2 rounded-4 overflow-hidden">
            <div class="card-body bg-white px-3 py-1">
                <div class="row g-3 align-items-center justify-content-between">
                    {{-- Kiri: Info Jumlah Data (Optional, nice to have) --}}
                    <div class="col-12 col-md-6">
                        <h5 class="m-0 fw-bold text-secondary">
                            <i class="bi bi-table me-2"></i>Data Records
                        </h5>
                    </div>

                    {{-- Kanan: Filter Input --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 fw-bold text-muted" id="filter-icon">
                                <i class="bi bi-funnel-fill"></i>
                            </span>
                            <div class="form-floating grow">
                                <select id="filterPackage" class="form-select border-start-0 ps-2"
                                    aria-label="Filter Status">
                                    <option value="all" selected>Show All Status</option>
                                    <option value="open">Open</option>
                                    <option value="close">Close</option>
                                    <option value="follow_up">Follow Up 1</option>
                                    <option value="follow_up_on_delay">Follow Up 1 Delay</option>
                                </select>
                                <label for="filterPackage" style="padding-left: 0.5rem; opacity: 0.6;">Filter by
                                    Status</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE SECTION --}}
        {{-- Gunakan .table-container dari app.scss untuk Sticky Header --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-container p-3" style="min-height: 400px;">
                    @if (isset($Problems) && count($Problems) > 0)
                        {{--
                        Refactor Note:
                        Pastikan tabel di dalam @include memiliki ID 'dataTable'
                        atau class 'table' agar script filter di bawah jalan.
                    --}}
                        @switch($type)
                            @case('leader-performance')
                                @include('problem_list.table._leader-performance', [
                                    'Problems' => $Problems,
                                ])
                            @break

                            @case('leader-consistency')
                                @include('problem_list.table._leader-consistency', [
                                    'Problems' => $Problems,
                                    'now' => $now,
                                ])
                            @break

                            @case('supervisor-performance')
                                @include('problem_list.table._supervisor-performance', [
                                    'Problems' => $Problems,
                                ])
                            @break

                            @case('supervisor-consistency')
                                @include('problem_list.table._supervisor-consistency', [
                                    'Problems' => $Problems,
                                    'now' => $now,
                                ])
                            @break

                            @default
                                <div class="text-center py-5">
                                    <i class="bi bi-question-circle display-1 text-muted"></i>
                                    <p class="mt-3">Invalid Category Type</p>
                                </div>
                        @endswitch
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-5 animate-fade-in">
                            <img src="{{ asset('image/no-data.svg') }}" alt="Empty" style="width: 150px; opacity: 0.5;"
                                onerror="this.style.display='none'">
                            <i class="bi bi-clipboard-x display-1 text-muted mb-3 d-block"></i>
                            <h4 class="text-muted fw-bold">No Data Found</h4>
                            <p class="text-secondary">There are no problem records for this category yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- STICKY ACTION BAR (Footer) --}}
    <div class="action-bar d-flex justify-content-between align-items-center px-4 my-0 py-1">
        <a href="{{ route('listProblem.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
            <i class="bi bi-arrow-left me-2"></i> Back to List
        </a>

        {{-- Bisa tambah tombol Export/Print disini kalau nanti butuh --}}
        <div class="d-none d-md-block text-muted small">
            Showing result for <strong>{{ $pageTitle }}</strong>
        </div>
    </div>

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // --- Client Side Filtering Logic ---
            // Script ini akan menyembunyikan baris tabel berdasarkan status yang dipilih.
            // SYARAT: Di file _leader-performance.blade.php (dan lainnya),
            // <tr> harus punya class atau data-attribute yang sesuai, misal:
            // <tr data-status="open"> ... </tr>

            $('#filterPackage').on('change', function() {
                const filterVal = $(this).val().toLowerCase();
                const tableRows = $('table tbody tr'); // Asumsi ada <table> di dalam include

                if (filterVal === 'all' || filterVal === '') {
                    tableRows.fadeIn(200); // Show All
                } else {
                    tableRows.each(function() {
                        // Cari status di dalam text kolom (kalau tidak ada data-status)
                        // Atau lebih baik gunakan atribut: $(this).data('status')
                        const rowText = $(this).text().toLowerCase();
                        const rowStatus = $(this).attr('data-status') ||
                            ''; // Opsional: Tambahkan atribut ini di blade tabel

                        // Logic match: Cek atribut dulu, kalau ga ada cek text
                        if (rowStatus.includes(filterVal) || rowText.includes(filterVal.replace('_',
                                ' '))) {
                            $(this).fadeIn(200);
                        } else {
                            $(this).fadeOut(200); // Hide
                        }
                    });
                }
            });
        });
    </script>
@endsection
