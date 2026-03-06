@extends('layouts.app')

@push('subtitle')
    <div class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase text-center">
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
    </div>
@endpush

@section('content')
    <div class="px-5 my-5">
        {{-- Form method GET biar data filter masuk ke URL (bisa di-bookmark/share) --}}
        <form method="GET">

            {{-- DYNAMIC FILTER INPUTS --}}
            @switch($type)
                @case('leader')
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <label for="department"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Department</label>
                        <select name="department" id="department" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <label for="supervisor"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Supervisor</label>
                        <select name="supervisor" id="supervisor" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Supervisor --</option>
                            @foreach ($supervisors as $supervisor)
                                <option value="{{ $supervisor->employeeID }}">{{ $supervisor->employeeID }} -
                                    {{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <label for="leader"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Leader</label>
                        <select name="leader" id="leader" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Leader --</option>
                            @foreach ($leaders as $leader)
                                <option value="{{ $leader->employeeID }}">{{ $leader->employeeID }} - {{ $leader->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @break

                @case('supervisor')
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <label for="department"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Department</label>
                        <select name="department" id="department" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <label for="supervisor"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Supervisor</label>
                        <select name="supervisor" id="supervisor" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Supervisor --</option>
                            @foreach ($supervisors as $supervisor)
                                <option value="{{ $supervisor->employeeID }}">{{ $supervisor->employeeID }} -
                                    {{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @break

                @case('operator')
                    <div class="d-flex align-items-center gap-2 mb-3 w-100">
                        <label for="department"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Department</label>
                        <select name="department" id="department" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3 w-100">
                        <label for="supervisor"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Supervisor</label>
                        <select name="supervisor" id="supervisor" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Supervisor --</option>
                            @foreach ($supervisors as $supervisor)
                                <option value="{{ $supervisor->employeeID }}">{{ $supervisor->employeeID }} -
                                    {{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3 w-100">
                        <label for="leader"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Leader</label>
                        <select name="leader" id="leader" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Leader --</option>
                            @foreach ($leaders as $leader)
                                <option value="{{ $leader->employeeID }}">{{ $leader->employeeID }} - {{ $leader->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3 w-100">
                        <label for="operator"
                            class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Operator</label>
                        <select name="operator" id="operator" class="form-select bg-warning-subtle" required>
                            <option value="" selected disabled>-- Pilih Operator --</option>
                            {{-- FIX: Sebelumnya hardcode "Data", sekarang dilooping --}}
                            @foreach ($operators as $operator)
                                <option value="{{ $operator->employeeID }}">{{ $operator->employeeID }} - {{ $operator->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @break
            @endswitch

            {{-- PERIODE BULAN --}}
            <div class="d-flex align-items-center gap-2 mb-4">
                <p
                    class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">
                    Periode</p>
                <div class="d-flex align-items-center gap-2 w-100">
                    <label for="month"
                        class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white m-0">Month
                        & Year</label>
                    <input type="month" name="month" id="month" class="form-control bg-warning-subtle w-100"
                        required />
                </div>
            </div>

            {{-- DYNAMIC ACTION BUTTONS --}}
            @switch($type)
                @case('leader')
                    <div class="my-3 pb-5 d-flex justify-content-center align-items-center gap-4">
                        <button type="submit" formaction="{{ route('reports.monthly', ['type' => $type]) }}"
                            class="btn btn-primary btn-lg py-3 fs-5 rounded-4 text-uppercase fw-bold shadow-sm w-50">View Leader
                            Consistency Report</button>
                        <button type="submit" formaction="{{ route('reports.score', ['type' => $type]) }}"
                            class="btn btn-primary btn-lg py-3 fs-5 rounded-4 text-uppercase fw-bold shadow-sm w-50">View Score
                            Leader Report</button>
                    </div>
                @break

                @case('supervisor')
                    <div class="my-3 pb-5 d-flex justify-content-center align-items-center gap-4">
                        {{-- FIX: Ganti <a> jadi <button> biar form disubmit --}}
                        <button type="submit" formaction="{{ route('reports.monthly', ['type' => $type]) }}"
                            class="btn btn-primary btn-lg py-3 fs-5 rounded-4 text-uppercase fw-bold shadow-sm w-50">View Supervisor
                            Consistency Report</button>
                        <button type="submit" formaction="{{ route('reports.score', ['type' => $type]) }}"
                            class="btn btn-primary btn-lg py-3 fs-5 rounded-4 text-uppercase fw-bold shadow-sm w-50">View Score
                            Supervisor Report</button>
                    </div>
                @break

                @case('operator')
                    <div class="my-3 pb-1 d-flex justify-content-center align-items-center gap-4">
                        {{-- FIX: Ganti <a> jadi <button> --}}
                        <button type="submit" formaction="{{ route('reports.score', ['type' => $type]) }}"
                            class="btn btn-primary btn-lg py-3 fs-5 rounded-4 text-uppercase fw-bold shadow-sm w-50">View Score
                            Performance Report</button>
                    </div>
                @break
            @endswitch

            <div>
                <a href="{{ route('reports.index') }}"
                    class="btn btn-secondary text-white py-2 px-4 rounded-pill fw-bold"><i
                        class="bi bi-arrow-left me-2"></i> Kembali</a>
            </div>
        </form>
    </div>
@endsection
