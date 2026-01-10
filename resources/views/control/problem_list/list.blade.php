@extends('layouts.app')

@push('subtitle')
<p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
    @switch($type)
    @case('leader-performance')
    List Leader Performance Problem
    @break
    @case('leader-consistency')
    List Leader Consistency Problem
    @break
    @case('supervisor-performance')
    List Supervisor Performance Problem
    @break
    @case('supervisor-consistency')
    List Supervisor Consistency Problem
    @break
    @default
    Tidak Vaild.
    @break
    @endswitch
</p>
@endpush

@section('content')
<div class="px-5">
    <form id="filterForm" class="w-100 d-flex justify-content-end gap-3 align-items-center mt-1">
        <label for="filterPackage">Filter</label>
        <select id="filterPackage" class="form-select w-auto">
            <option value="">-- Semua</option>
            <option value="open">Open</option>
            <option value="close">Close</option>
            <option value="follow_up">Follow Up +1</option>
            <option value="follow_up_on_delay">Follow Up +1 Delay</option>
        </select>
    </form>

    @switch($type)
    @case('leader-performance')
    @include('control.problem_list.table._leader-performance', ['Problems' => $Problems])
    @break
    @case('leader-consistency')
    @include('control.problem_list.table._leader-consistency', ['Problems' => $Problems])
    @break
    @case('supervisor-performance')
    @include('control.problem_list.table._supervisor-performance', ['Problems' => $Problems])
    @break
    @case('supervisor-consistency')
    @include('control.problem_list.table._supervisor-consistency', ['Problems' => $Problems])
    @break
    @default
    <div></div>
    @break
    @endswitch

    <div class="py-1 d-flex justify-content-between">
        <div>
            <a href="{{ route('control.listProblem.index') }}" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
    </div>
</div>
@endsection