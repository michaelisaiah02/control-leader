@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
        report
    </p>
@endpush

@section('content')
    <div class="px-5 my-5">
        <div class="row">
            <div class="col-md-6 my-auto d-flex w-100 flex-column justify-content-center align-items-center">
                <div class="my-4 col-12 col-md-4">
                    <a href="{{ route('reports.form', 'supervisor') }}"
                        class="btn btn-primary btn-lg h-100 py-3 fs-4 align-content-center rounded-4 text-uppercase">Supervisor
                        Performance Report</a>
                </div>
                <div class="my-4 col-12 col-md-8 d-flex gap-5 justify-content-center align-items-center">
                    <a href="{{ route('reports.form', 'leader') }}"
                        class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 text-uppercase">Leader
                        Performance Report</a>
                    <a href="{{ route('reports.form', 'operator') }}"
                        class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 text-uppercase">Operator
                        Performance Report</a>
                </div>
            </div>
        </div>
        <div class="h-100 py-5">
            <a href="{{ route('dashboard') }}" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
    </div>
@endsection
