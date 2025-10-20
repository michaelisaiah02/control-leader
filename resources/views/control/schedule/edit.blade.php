@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        Edit Schedule - {{ \Carbon\Carbon::create($plan->year, $plan->month)->format('F Y') }}
    </p>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm" id="addUserBtn">+ Tambah User</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle" id="scheduleTable">
                <thead class="table-light sticky-top">
                    <tr>
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
                                    <select class="form-select form-select-sm schedule-input"
                                        data-user="{{ $target['id'] }}" data-date="{{ $date }}"
                                        data-division="{{ $target['division'] }}" {{ $isDisabled ? 'disabled' : '' }}>
                                        <option value="">-</option>
                                        @foreach ([1, 2, 3] as $s)
                                            <option value="{{ $s }}" {{ $shift == $s ? 'selected' : '' }}>
                                                {{ $s }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const csrf = '{{ csrf_token() }}';
        const planId = '{{ $plan->id }}';

        // =================== Auto save =====================
        document.querySelectorAll('.schedule-input').forEach(select => {
            select.addEventListener('change', async () => {
                const userId = select.dataset.user;
                const date = select.dataset.date;
                const shift = select.value;
                const division = select.dataset.division;
                console.log(division);
                select.classList.add('border-warning');

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
                    select.classList.remove('border-warning');
                    select.classList.add('border-success');
                    setTimeout(() => select.classList.remove('border-success'), 1000);
                }
            });
        });

        // =================== Add User =====================
        document.getElementById('addUserBtn').addEventListener('click', async () => {
            const tbody = document.querySelector('#scheduleTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
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
                    <select class="form-select form-select-sm" disabled>
                    <option value="">-</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    </select>
                </td>
                @endfor
            `;
            tbody.appendChild(newRow);

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
                        division: divSelect.value,
                    })
                });
                const data = await res.json();
                console.log(data);
                if (data.success) {
                    alert('User berhasil ditambahkan ke jadwal!');
                    location.reload();
                }
                if (data.error) {
                    alert('Error: ' + data.error);
                }
            });
        });
    </script>
@endsection
