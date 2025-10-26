@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        Edit Schedule - {{ \Carbon\Carbon::create($plan->year, $plan->month)->format('F Y') }}
    </p>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm" id="addUserBtn" {{ $isPastMonth ? 'disabled' : '' }}>
                + Tambah User
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle" id="scheduleTable">
                <thead class="table-primary sticky-top">
                    <tr>
                        <th></th>
                        <th>User</th>
                        <th>Division</th>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            <th style="min-width: 50px">{{ $d }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($targets as $target)
                        <tr data-user="{{ $target['id'] }}">
                            <td>
                                <button type="button" class="btn btn-sm btn-danger delete-user"
                                    {{ $isPastMonth ? 'disabled' : '' }}><i class="bi bi-trash"></i></button>
                            </td>
                            <td>
                                <select class="form-select form-select-sm user-select"
                                    {{ $isCurrentMonth ? 'disabled' : '' }}>
                                    <option value="{{ $target['id'] }}">{{ $target['name'] }}</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm division-select"
                                    {{ $isCurrentMonth ? 'disabled' : '' }}>
                                    @foreach ($divisionOptions as $option)
                                        <option value="{{ $option->division_name }}"
                                            {{ $target['division'] === $option->division_name ? 'selected' : '' }}>
                                            {{ $option->division_name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $date = sprintf('%04d-%02d-%02d', $plan->year, $plan->month, $d);
                                    $shift = $target['dates'][$date] ?? '';
                                    $isDisabled = \Carbon\Carbon::parse($date)->lte($today);
                                @endphp
                                <td>
                                    <span class="badge shift-badge {{ $shift ? 'bg-primary' : 'bg-secondary' }}"
                                        data-user="{{ $target['id'] }}" data-date="{{ $date }}"
                                        data-shift="{{ $shift }}" data-division="{{ $target['division'] }}">
                                        {{ $shift ?: '-' }}
                                    </span>
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="position-fixed bottom-0 end-0 p-3   ">
            <div class="col-auto">
                <a href="{{ route('control.schedule.index') }}" class="btn btn-primary btn-sm mt-2">Kembali</a>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus user dari jadwal ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="module">
        const csrf = '{{ csrf_token() }}';
        const planId = '{{ $plan->id }}';

        // =================== Update shift on badge click =====================
        document.querySelectorAll('.shift-badge').forEach(el => {
            el.addEventListener('click', async () => {
                if (el.classList.contains('disabled')) return;
                const userId = el.dataset.user;
                const date = el.dataset.date;
                let shift = parseInt(el.dataset.shift) || 0;
                shift = shift >= 3 ? '' : shift + 1;
                const division = el.dataset.division;

                const res = await fetch(`/control/schedule/${planId}/update-cell`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        date,
                        shift,
                        division
                    })
                });

                const data = await res.json();
                if (data.success) {
                    el.dataset.shift = shift;
                    el.textContent = shift || '-';
                    el.className = 'badge shift-badge ' + (shift ? 'bg-primary' : 'bg-secondary');
                }
            });
        });

        // =================== Add User =====================
        document.getElementById('addUserBtn').addEventListener('click', async () => {
            const tbody = document.querySelector('#scheduleTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <button type="button" class="btn btn-sm btn-danger delete-user"><i class="bi bi-trash"></i></button>
                </td>
                <td>
                    <select class="form-select form-select-sm new-user">
                        <option value="">-- pilih user --</option>
                        @foreach ($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm new-division">
                        @foreach ($divisionOptions as $option)
                            <option value="{{ $option->division_name }}">{{ $option->division_name }}</option>
                        @endforeach
                    </select>
                </td>
                @for ($d = 1; $d <= $daysInMonth; $d++)
                    <td>
                        <span class="badge shift-badge bg-secondary">-</span>
                    </td>
                @endfor
            `;
            tbody.appendChild(newRow);

            // allow removing unsaved row before it is persisted
            newRow.querySelector('.delete-user').addEventListener('click', () => newRow.remove());

            const userSelect = newRow.querySelector('.new-user');
            const divSelect = newRow.querySelector('.new-division');

            userSelect.addEventListener('change', async () => {
                if (!userSelect.value) return;
                const res = await fetch(`/control/schedule/${planId}/add-user`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userSelect.value,
                        division: divSelect.value
                    })
                });
                const data = await res.json();
                if (data.success) {
                    // reload to render the saved user with correct data attributes and controls
                    window.location.reload();
                }
            });
        });

        // =================== Delete User (existing rows) =====================
        const tbodyEl = document.querySelector('#scheduleTable tbody');
        const deleteModalEl = document.getElementById('deleteModal');
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        let rowToDelete = null;

        tbodyEl.addEventListener('click', (e) => {
            const btn = e.target.closest('.delete-user');
            if (!btn) return;

            const tr = btn.closest('tr');
            const userId = tr.dataset.user;

            // if row is unsaved (no userId), remove immediately without server call
            if (!userId) {
                tr.remove();
                return;
            }

            rowToDelete = tr;
            deleteModalEl.dataset.userId = userId;
            deleteModal.show();
        });

        document.getElementById('confirmDelete').addEventListener('click', async () => {
            const userId = deleteModalEl.dataset.userId;
            if (!userId) {
                deleteModal.hide();
                return;
            }

            const res = await fetch(`/control/schedule/${planId}/remove-user/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf
                }
            });
            const data = await res.json();
            if (data.success && rowToDelete) {
                rowToDelete.remove();
            }
            deleteModal.hide();
            rowToDelete = null;
            deleteModalEl.dataset.userId = '';
        });
    </script>
@endsection
