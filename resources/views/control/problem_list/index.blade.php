@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
        list problem
    </p>
@endpush

@section('content')
<div class="px-5">
    <div class="d-flex justify-content-between align-items-center">
        <a href="" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
            List Leader Performance Problem
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                99+
                <span class="visually-hidden">unread messages</span>
            </span>
        </a>
        <a href="" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
            List Leader Consistency Problem
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                99+
                <span class="visually-hidden">unread messages</span>
            </span>
        </a>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <a href="" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
            List Supervisor Performance Problem
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                99+
                <span class="visually-hidden">unread messages</span>
            </span>
        </a>
        <a href="" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
            List Supervisor Consistency Problem
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                99+
                <span class="visually-hidden">unread messages</span>
            </span>
        </a>
    </div>
    <div class="py-1 d-flex justify-content-between">
        <div>
            <a href="#" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
    </div>
</div>
@endsection