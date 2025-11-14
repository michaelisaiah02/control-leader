@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
        report
    </p>
@endpush

@section('content')
<div class="px-5">
    <div class="d-flex justify-content-center align-items-center">
        <a href="" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">Supervisor Performance Report</a>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <a href="" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">Leader Performance Report</a>
        <a href="" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">Operator Performance Report</a>
    </div>
    <div class="py-1 d-flex justify-content-between">
        <div>
            <a href="#" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
    </div>
</div>
@endsection