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
                    <label for="user_id" class="form-label mb-0">Filter User:</label>
                </div>
                <div class="col-auto">
                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                        <option value="">-- Semua User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle" id="scheduleTable">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>User</th>
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
                            <td>{{ $plan->scheduler->name }}</td>
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
        <div class="py-0 d-flex justify-content-between align-items-center">
            <button class="btn btn-primary btn-lg text-white rounded-circle" data-bs-toggle="modal"
                data-bs-target="#scheduleModal">&plus;</button>
            <a href="{{ route('dashboard') }}" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
    </div>

    <!-- Modal Tambah/Edit User -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" target="{{ route('control.schedule.store') }}"
                id="scheduleForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">Add Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="employeeID" class="form-label">Jadwal Untuk</label>
                        <select id="employeeID" name="employeeID" class="form-select text-capitalize" required>
                            <option value="">Pilih User...</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->employeeID }}" class="text-capitalize">{{ $user->name }} -
                                    {{ $user->role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="month" class="form-label">Month</label>
                        <input type="month" class="form-control" id="month" name="month"
                            min="{{ now()->format('Y-m') }}" required>
                        <div class="invalid-feedback">Bulan dengan tahun yang sama sudah pernah dibuat.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <x-toast />
@endsection
@section('scripts')
    <script type="module">
        $(document).ready(function() {
            $('.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
        });
    </script>
@endsection
