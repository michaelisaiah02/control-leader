@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
        list problem
    </p>
@endpush

@section('content')
    <div class="px-5 my-5">
        <div class="d-flex flex-column gap-5 justify-content-center align-items-center">
            @if (auth()->user()->role === 'leader')
                <div class="d-flex justify-content-between gap-5 align-items-center">
                    <div class="my-2 col-12 col-md-12 d-flex gap-5 justify-content-center align-items-center">
                        <a href="{{ route('listProblem.list', 'leader-performance') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
                            List Leader Performance Problem
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                99+
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </a>
                        <a href="{{ route('listProblem.list', 'leader-consistency') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
                            List Leader Consistency Problem
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                99+
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </a>
                    </div>
                </div>
            @else
                <div class="d-flex justify-content-between gap-5 align-items-center">
                    <div class="my-2 col-12 col-md-12 d-flex gap-5 justify-content-center align-items-center">
                        <a href="{{ route('listProblem.list', 'leader-performance') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
                            List Leader Performance Problem
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                99+
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </a>
                        <a href="{{ route('listProblem.list', 'leader-consistency') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
                            List Leader Consistency Problem
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                99+
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-5 align-items-center">
                    <div class="my-2 col-12 col-md-12 d-flex gap-5 justify-content-center align-items-center">
                        <a href="{{ route('listProblem.list', 'supervisor-performance') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
                            List Supervisor Performance Problem
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                99+
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </a>
                        <a href="{{ route('listProblem.list', 'supervisor-consistency') }}"
                            class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">
                            List Supervisor Consistency Problem
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                99+
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
        <div class="h-100 pt-5">
            <a href="{{ route('dashboard') }}" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
    </div>
@endsection
