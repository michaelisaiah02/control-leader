@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        Daftar Jadwal
    </p>
@endpush

@section('content')
    <div class="container-fluid mt-3">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle" id="scheduleTable">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Jumlah User</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $index => $plan)
                        <tr>
                            <td>{{ $index + 1 }}</td>
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
