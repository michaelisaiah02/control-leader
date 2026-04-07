@extends('layouts.app')

@section('styles')
    <style>
        /* Efek kartu keangkat dikit pas di-hover biar interaktif */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
            border-color: var(--bs-primary) !important;
        }
    </style>
@endsection

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-bar-chart-line me-2 fs-5"></i>
        <span class="fs-5 fw-bold text-uppercase">Reports</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid dashboard-container max-w-800 mx-auto animate-fade-in pb-5 pb-md-0">

        <div class="text-center mt-auto mb-5">
            <h4 class="fw-bold text-dark">Pilih Kategori Report</h4>
            <p class="text-muted small">Pilih level jabatan untuk melihat laporan performa</p>
        </div>

        <div class="row g-4 justify-content-center mb-auto">
            @if (auth()->user()->role !== 'leader')
                {{-- KARTU SUPERVISOR --}}
                <div class="col-12 col-md-4">
                    <a href="{{ route('reports.form', 'supervisor') }}" class="text-decoration-none">
                        <div class="card border border-light shadow-sm rounded-4 h-100 hover-lift text-center p-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="bi bi-person-workspace fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1 text-uppercase">Supervisor</h5>
                            <p class="text-muted small mb-0">Performance Report</p>
                        </div>
                    </a>
                </div>
            @endif

            {{-- KARTU LEADER --}}
            <div class="col-12 col-md-4">
                <a href="{{ route('reports.form', 'leader') }}" class="text-decoration-none">
                    <div class="card border border-light shadow-sm rounded-4 h-100 hover-lift text-center p-4">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-person-badge fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1 text-uppercase">Leader</h5>
                        <p class="text-muted small mb-0">Performance Report</p>
                    </div>
                </a>
            </div>

            {{-- KARTU OPERATOR --}}
            <div class="col-12 col-md-4">
                <a href="{{ route('reports.form', 'operator') }}" class="text-decoration-none">
                    <div class="card border border-light shadow-sm rounded-4 h-100 hover-lift text-center p-4">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-person-gear fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1 text-uppercase">Operator</h5>
                        <p class="text-muted small mb-0">Performance Report</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

    {{-- STICKY ACTION BAR --}}
    <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center"
        style="z-index: 1030;">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
            <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
        </a>
    </div>
@endsection
