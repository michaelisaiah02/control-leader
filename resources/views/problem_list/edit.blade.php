@extends('layouts.app')

@php
$map = [
'leader-performance' => 'edit leader performance problem',
'leader-consistency' => 'edit leader consistency problem',
'supervisor-performance' => 'edit supervisor performance problem',
'supervisor-consistency' => 'edit supervisor consistency problem',
];

$role = explode('-', $type)[0];

@endphp


@push('subtitle')
<div
    class="d-inline-flex align-items-center justify-content-center px-3 py-1 mt-1 mb-0 rounded-3 bg-white bg-opacity-10 border border-light text-white subtitle">
    <i class="bi bi-pencil-square me-2 fs-6"></i>
    <span class="fs-6 fw-bold text-uppercase">{{ $map[$type] ?? 'tidak vaild' }}</span>
</div>
@endpush

@section('content')
<form method="POST" action="{{ route('listProblem.update', ['type' => $type, 'id' => $problem->id]) }}" class="px-5">
    @csrf
    @method('PUT')
    <div class="d-flex flex-column gap-1">
        <!-- Read-Only -->
        @switch($type)
        @case('leader-performance')
        @include('problem_list.form._performance')
        @break
        @case('leader-consistency')
        @include('problem_list.form._consistency')
        @break
        @case('supervisor-performance')
        @include('problem_list.form._performance')
        @break
        @case('supervisor-consistency')
        @include('problem_list.form._consistency')
        @break
        @default
        <div></div>
        @endswitch
        <div class="d-flex justify-content-between w-100">
            <div class="d-flex align-items-center gap-2 w-100">
                <label for="countermeasure" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Countermeasure</label>
                <textarea name="countermeasure" id="countermeasure" class="form-control bg-primary-subtle" {{ auth()->user()->role !== 'leader' ? 'disabled' : "" }}>{{ $problem->countermeasure }}</textarea>
            </div>
        </div>

        <!-- Non Read -->
        <div class="d-flex justify-content-between w-100">
            <div class="d-flex align-items-center gap-2 w-100">
                <label for="department" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Status</label>
                <select name="department" id="department" class="form-control bg-warning-subtle" required {{ auth()->user()->role !== 'supervisor' ? 'disabled' : "" }}>
                    <option value="" selected disabled>Open / Close / Follow Up 1</option>
                    <option value="open" {{ $problem->status == 'open' ? 'selected' : "" }}>Open</option>
                    <option value="close" {{ $problem->status == 'close' ? 'selected' : "" }}>Close</option>
                    <option value="follow_up_1" {{ $problem->status == 'follow_up_1' ? 'selected' : "" }}>Follow Up 1</option>
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-between w-100">
            <div class="d-flex align-items-center gap-2 w-100">
                <label for="due_date" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Due Date</label>
                <input type="date" name="due_date" id="due_date" class="form-control bg-warning-subtle" value="{{ $problem->due_date === null ? '' : \Carbon\Carbon::parse($problem->due_date)->format('Y-m-d') }}" required {{ auth()->user()->role !== 'supervisor' || $problem->due_date !== null ? 'disabled' : "" }}>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="py-1 d-flex justify-content-between">
        <div>
            <a href="{{ route('listProblem.list', $type) }}" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
        <div>
            <button type="submit" class="btn btn-primary text-white py-2 px-4">Save</button>
        </div>
    </div>
</form>
@endsection