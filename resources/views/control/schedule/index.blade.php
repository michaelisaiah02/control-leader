@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        Daftar Jadwal
    </p>
@endpush

@section('content')
    <div class="container-fluid mt-3">
        <form action="" method="GET" class="mb-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <label for="user_id" class="form-label mb-0">Pilih User:</label>
                </div>
                <div class="col-auto">
                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                        <option value="">-- Semua User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success btn-sm">Filter</button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle" id="scheduleTable">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Jumlah User</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $plan)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::create($plan->year, $plan->month)->format('F') }}</td>
                            <td>{{ $plan->year }}</td>
                            <td>{{ $plan->details_count }}</td>
                            <td>
                                <a href="{{ route('control.schedule.edit', $plan->id) }}" class="btn btn-primary btn-sm">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
