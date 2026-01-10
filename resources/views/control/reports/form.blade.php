@extends('layouts.app')

@push('subtitle')
<p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
    @switch($type)
    @case('leader')
    Leader Performance Report
    @break
    @case('supervisor')
    Supervisor Performance Report
    @break
    @case('operator')
    Operator Performance Report
    @break
    @default
    Performance Report
    @endswitch
</p>
@endpush

@section('content')
<div class="px-5 my-5">
    @switch($type)
    @case('leader')
    <div class="d-flex align-items-center gap-2">
        <label for="department" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Department</label>
        <select name="department" id="department" class="form-control bg-warning-subtle" required>
            <option value="" selected disabled>Gedung 1 Production Hose/Gedung 2 Finishing Molding, dst.</option>
            <option value="">Data</option>
        </select>
    </div>
    <div class="d-flex align-items-center gap-2">
        <label for="supervisor_name" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Supervisor</label>
        <select name="supervisor_name" id="supervisor_name" class="form-control bg-warning-subtle" required>
            <option value="" selected disabled>ID - Nama Supervisor</option>
            <option value="">Data</option>
        </select>
    </div>
    <div class="d-flex align-items-center gap-2">
        <label for="leader_name" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Leader</label>
        <select name="leader_name" id="leader_name" class="form-control bg-warning-subtle" required>
            <option value="" selected disabled>ID - Nama Leader</option>
            <option value="">Data</option>
        </select>
    </div>
    @break
    @case('supervisor')
    <div class="d-flex align-items-center gap-2">
        <label for="department" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Department</label>
        <select name="department" id="department" class="form-control bg-warning-subtle" required>
            <option value="" disabled>Gedung 1 Production Hose/Gedung 2 Finishing Molding, dst.</option>
            <option value="">Data</option>
        </select>
    </div>
    <div class="d-flex align-items-center gap-2">
        <label for="supervisor_name" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Supervisor</label>
        <select name="supervisor_name" id="supervisor_name" class="form-control bg-warning-subtle" required>
            <option value="" disabled>ID - Nama Supervisor</option>
            <option value="">Data</option>
        </select>
    </div>
    @break
    @case('operator')
    <div class="d-flex align-items-center gap-2 w-100">
        <label for="department" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Department</label>
        <select name="department" id="department" class="form-control bg-warning-subtle" required>
            <option value="" selected disabled>Gedung 1 Production Hose/Gedung 2 Finishing Molding, dst.</option>
            <option value="">Data</option>
        </select>
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
        <label for="supervisor_name" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Supervisor</label>
        <select name="supervisor_name" id="supervisor_name" class="form-control bg-warning-subtle" required>
            <option value="" selected disabled>ID - Nama Supervisor</option>
            <option value="">Data</option>
        </select>
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
        <label for="leader_name" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Leader</label>
        <select name="leader_name" id="leader_name" class="form-control bg-warning-subtle" required>
            <option value="" selected disabled>ID - Nama Leader</option>
            <option value="">Data</option>
        </select>
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
        <label for="operator_name" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Operator</label>
        <select name="operator_name" id="operator_name" class="form-control bg-warning-subtle" required>
            <option value="" selected disabled>ID - Nama Operator</option>
            <option value="">Data</option>
        </select>
    </div>
    @break
    @default
    <p>Role Tidak Vaild.</p>
    @break
    @endswitch

    <div class="d-flex align-items-center gap-2">
        <p class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Periode</p>
        <div class="d-flex justify-content-center align-items-center gap-2 w-100">
            <div class="d-flex align-items-center gap-2 w-100">
                <label for="month" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Month & Year</label>
                <input type="month" name="month" id="month" class="form-control bg-warning-subtle w-100" required />
            </div>
        </div>
    </div>

    @switch($type)
    @case('leader')
    <div class="my-3 pb-5 d-flex justify-content-center align-items-center gap-5">
        <a href="{{ route('control.reports.monthly', ['type' => $type]) }}" class="btn btn-primary btn-lg h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">View Leader Consistency Report</a>
        <a href="{{ route('control.reports.score', ['type' => $type]) }}" class="btn btn-primary btn-lg h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">View Score Leader Report</a>
    </div>
    @break
    @case('supervisor')
    <div class="my-3 pb-5 d-flex justify-content-center align-items-center gap-5">
        <a href="{{ route('control.reports.monthly', ['type' => $type]) }}" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">View Supervisor Consistency Report</a>
        <a href="{{ route('control.reports.score', ['type' => $type]) }}" class="btn btn-primary btn-lg w-100 h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">View Score Supervisor Report</a>
    </div>
    @break
    @case('operator')
    <div class="my-3 pb-1 d-flex justify-content-center align-items-center gap-5">
        <a href="{{ route('control.reports.score', ['type' => $type]) }}" class="btn btn-primary btn-lg h-100 py-3 fs-4 align-content-center rounded-4 position-relative text-uppercase">View Score Performance Report</a>
    </div>
    @break
    @default
    <div></div>
    @break
    @endswitch

    <div>
        <a href="{{ route('control.reports.index') }}" class="btn btn-primary text-white py-2 px-4">Back</a>
    </div>
</div>
@endsection