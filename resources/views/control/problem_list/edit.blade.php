@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
        @switch($type)
            @case('leader-performance')
                edit leader performance problem
                @break
            @case('leader-consistency')
                edit leader consistency problem
                @break
            @case('supervisor-performance')
                edit supervisor performance problem
                @break
            @case('supervisor-consistency')
                edit supervisor consistency problem
                @break
            @default
                tidak vaild.
                @break
        @endswitch
    </p>
@endpush

@section('content')
    <form method="POST" action="{{ route('control.listProblem.update', ['type' => $type, 'id' => 1]) }}" class="px-5">
        @csrf
        @method('PUT')
        <div class="d-flex flex-column gap-1">
            <!-- Read-Only -->
            @switch($type)
                @case('leader-performance')
                    @include('control.problem_list.form.performance')
                    @break
                @case('leader-consistency')
                    @include('control.problem_list.form.consistency')
                    @break
                @case('supervisor-performance')
                    @include('control.problem_list.form.performance')
                    @break
                @case('supervisor-consistency')
                    @include('control.problem_list.form.consistency')
                    @break
                @default
                    <div></div>
            @endswitch
            <div class="d-flex justify-content-between w-100">
                <div class="d-flex align-items-center gap-2 w-100">
                    <label for="countermeasure" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Countermeasure</label>
                    <textarea name="countermeasure" id="countermeasure" class="form-control bg-primary-subtle"></textarea>
                </div>
            </div>

            <!-- Non Read -->
            <div class="d-flex justify-content-between w-100">
                <div class="d-flex align-items-center gap-2 w-100">
                    <label for="department" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Status</label>
                    <select name="department" id="department" class="form-control bg-warning-subtle" required>
                        <option value="" selected disabled>Open / Close / Follow Up 1</option>
                        <option value="open">Open</option>
                        <option value="close">Close</option>
                        <option value="follow_up_1">Follow Up 1</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-between w-100">
                <div class="d-flex align-items-center gap-2 w-100">
                    <label for="due_date" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Due Date</label>
                    <input type="date" name="due_date" id="due_date" class="form-control bg-warning-subtle" required>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="py-1 d-flex justify-content-between">
            <div>
                <a href="{{ route('control.admin.question.index') }}" class="btn btn-primary text-white py-2 px-4">Back</a>
            </div>
            <div>
                <button type="submit" class="btn btn-primary text-white py-2 px-4">Save</button>
            </div>
        </div>
    </form>
@endsection
