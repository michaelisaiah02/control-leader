@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        SCHEDULE CONTROL LEADER
    </p>
@endpush

@section('styles')
    <style>
        .total-separator td {
            border: none !important;
            height: 8px;
            /* jarak */
        }

        /* Kolom User */
        td.user-col,
        th.user-col {
            min-width: 150px;
        }

        /* Kolom Division */
        td.division-col,
        th.division-col {
            min-width: 120px;
        }

        /* Shift Select */
        .shift-select {
            min-width: 45px !important;
            padding-left: 4px;
            padding-right: 18px;
            /* space for caret */
        }

        /* Division Select */
        .division-select {
            min-width: 90px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-between mb-3">
            <div class="col-auto">
                <form action="{{ route('schedule.index') }}" method="get">
                    <input type="month" name="month" id="monthPicker" value="{{ $plan->year }}-{{ $plan->month }}"
                        class="form-control form-control-sm" onchange="this.form.submit()" />
                </form>
            </div>
        </div>
        @empty($targets)
            <div class="alert alert-info">
                No users assigned to this schedule yet. Please add users to begin scheduling.
            </div>
        @else
            <div class="table-responsive">
                @php
                    $usedUserIDs = collect($targets)->pluck('id')->toArray();
                    $unusedUsers = $availableUsers->whereNotIn('employeeID', $usedUserIDs);
                    $hasEmptyRow = $unusedUsers->count() > 0;
                @endphp
                <table class="table table-bordered table-hover table-sm text-center align-middle text-nowrap"
                    style="width: 1500px" id="scheduleTable">
                    <thead class="table-primary sticky-top">
                        <tr>
                            <th class="user-col">User</th>
                            <th class="division-col">Division</th>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <th style="min-width: 50px">{{ $d }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $disabled = $isPastMonth ? 'disabled' : '';
                        @endphp
                        @foreach ($targets as $target)
                            <tr data-user="{{ $target['id'] }}">
                                <td class="user-col">
                                    <select class="form-select form-select-sm user-select" {{ $disabled }}>
                                        <option value="{{ $target['id'] }}">{{ $target['name'] }}</option>
                                    </select>
                                </td>
                                <td class="division-col">
                                    <select class="form-select form-select-sm division-select" {{ $disabled }}>
                                        @foreach ($divisionOptions as $option)
                                            <option value="{{ $option->name }}"
                                                {{ $target['division'] === $option->name ? 'selected' : '' }}>
                                                {{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $date = sprintf('%04d-%02d-%02d', $plan->year, $plan->month, $d);
                                        $shift = $target['dates'][$date] ?? '';
                                    @endphp
                                    <td>
                                        <select class="form-select form-select-sm shift-select" data-user="{{ $target['id'] }}"
                                            data-date="{{ $date }}" data-division="{{ $target['division'] }}"
                                            {{ $disabled }}>
                                            <option value="">-</option>
                                            @foreach (['1', '2', '3', 'L'] as $option)
                                                <option value="{{ $option }}"
                                                    {{ (string) $shift === $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                        @if ($hasEmptyRow)
                            <tr class="empty-row">
                                <td>
                                    <select class="form-select form-select-sm user-select">
                                        <option value="">-- pilih user --</option>
                                        @foreach ($availableUsers as $u)
                                            @if (!in_array($u->employeeID, $usedUserIDs))
                                                <option value="{{ $u->employeeID }}">{{ $u->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <select class="form-select form-select-sm division-select" disabled>
                                        <option value="">-- pilih user dulu --</option>
                                        @foreach ($divisionOptions as $option)
                                            <option value="{{ $option->name }}">{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    <td>
                                        <select class="form-select form-select-sm shift-select" disabled
                                            data-date="{{ sprintf('%04d-%02d-%02d', $plan->year, $plan->month, $d) }}">
                                            <option value="">-</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="L">L</option>
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        @endif
                        <tr id="totalSeparator" class="total-separator">
                            <td colspan="{{ 3 + $daysInMonth }}" class="p-0"></td>
                        </tr>
                        <template id="emptyRowTemplate">
                            <tr class="empty-row" data-user="">
                                <td>
                                    <select class="form-select form-select-sm user-select">
                                        <option value="">-- pilih user --</option>
                                        @foreach ($availableUsers as $user)
                                            @if (!in_array($user->employeeID, $usedUserIDs))
                                                <option value="{{ $user->employeeID }}">{{ $user->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <select class="form-select form-select-sm division-select" disabled>
                                        <option value="">-- pilih divisi --</option>
                                        @foreach ($divisionOptions as $d)
                                            <option value="{{ $d->name }}">{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    <td>
                                        <select class="form-select form-select-sm shift-select"
                                            data-date="{{ sprintf('%04d-%02d-%02d', $plan->year, $plan->month, $d) }}"
                                            disabled>
                                            <option value="">-</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="L">L</option>
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr id="totalRow" class="bg-dark text-white fw-bold">
                            <td colspan="2" class="text-start px-3">Total</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <td class="total-day" data-day="{{ $d }}">0</td>
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endempty
        <div class="position-fixed bottom-0 end-0 p-3   ">
            <div class="col-auto">
                <a href="{{ route('schedule.index') }}" class="btn btn-primary btn-sm mt-2">Kembali</a>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @if (!empty($targets))
        <script type="module">
            const isPastMonth = {{ $isPastMonth ? 'true' : 'false' }};
            const usedUsers = @json($usedUserIDs);
            async function post(url, data = {}) {
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(data)
                });

                if (!res.ok) throw new Error("HTTP Error");
                return res.json();
            }

            function bindEvents() {
                document.querySelector("#scheduleTable tbody")
                    .addEventListener("change", function(e) {

                        // SHIFT
                        if (e.target.classList.contains("shift-select")) {
                            handleShiftChange(e.target);
                        }

                        // DIVISION
                        if (e.target.classList.contains("division-select")) {
                            handleDivisionChange(e.target);
                        }

                        // USER SELECT
                        if (e.target.classList.contains("user-select")) {
                            handleUserSelect(e.target);
                        }
                    });
            }

            // -----------------------------------
            // HITUNG TOTAL PER HARI
            // -----------------------------------
            function calculateTotals() {
                const totals = {};
                document.querySelectorAll(".total-day").forEach(td => {
                    totals[td.dataset.day] = 0;
                });

                const countable = ["1", "2", "3"];

                document.querySelectorAll(".shift-select").forEach(sel => {
                    if (countable.includes(sel.value)) {
                        const day = Number(sel.dataset.date.split("-")[2]);
                        totals[day]++;
                    }
                });

                document.querySelectorAll(".total-day").forEach(td => {
                    td.textContent = totals[td.dataset.day] ?? 0;
                });
            }

            function updateCellColors() {
                document.querySelectorAll(".shift-select").forEach(sel => {
                    // reset dulu
                    sel.classList.remove("bg-danger");

                    // kalau L -> warnai merah
                    if (sel.value === "L") {
                        sel.classList.add("bg-danger");
                    }
                });
            }

            function addNewEmptyRow() {

                // Kalau user sudah habis → STOP
                if (!hasUnusedUsers()) return;

                const tpl = document.querySelector("#emptyRowTemplate");
                const clone = tpl.content.cloneNode(true);
                const tbody = document.querySelector("#scheduleTable tbody");
                const totalSeparator = document.querySelector("#totalSeparator");

                tbody.insertBefore(clone, totalSeparator);

                let newRow = tbody.querySelectorAll(".empty-row");
                newRow = newRow[newRow.length - 1];
                newRow.dataset.user = "";
                const userSelect = newRow.querySelector(".user-select");

                // Filter user yg sudah terpakai
                filterUserOptions(userSelect);
            }

            function hasUnusedUsers() {
                const usedSet = new Set(
                    [...document.querySelectorAll("tr[data-user]")].map(r => r.dataset.user)
                );

                const allUsers = @json($availableUsers->pluck('employeeID'));

                return allUsers.some(u => !usedSet.has(u));
            }

            function updateAllUserDropdowns() {
                const usedSet = new Set(
                    [...document.querySelectorAll("tr[data-user]")].map(r => r.dataset.user)
                );

                document.querySelectorAll(".empty-row .user-select").forEach(sel => {
                    [...sel.options].forEach(opt => {
                        if (usedSet.has(opt.value)) {
                            opt.hidden = true;
                        } else {
                            opt.hidden = false;
                        }
                    });
                });
            }

            async function handleShiftChange(sel) {
                const row = sel.closest("tr");
                const userId = row.dataset.user || row.querySelector(".user-select").value;
                const division = row.querySelector(".division-select").value;
                const date = sel.dataset.date;
                const shift = sel.value;

                if (!userId) {
                    alert("Pilih user dulu.");
                    sel.value = "";
                    return;
                }

                if (!division) {
                    alert("Pilih division dulu.");
                    sel.value = "";
                    return;
                }

                try {
                    const res = await post("{{ route('schedule.updateCell', $plan->id) }}", {
                        user_id: userId,
                        date,
                        shift,
                        division,
                    });

                    if (!res.success) throw new Error();

                    // Kini row bukan empty lagi
                    const wasEmpty = row.classList.contains("empty-row");
                    row.classList.remove("empty-row");
                    row.dataset.user = userId;

                    if (wasEmpty) {
                        addNewEmptyRow();
                    }

                    calculateTotals();
                    updateCellColors();

                } catch (err) {
                    console.error("SHIFT ERROR:", err);
                    alert("Gagal update shift.");
                }
            }

            async function handleDivisionChange(sel) {
                if (isPastMonth) return;

                const row = sel.closest("tr");
                const userId = row.dataset.user;
                const division = sel.value;

                // kalau belum ada shift, ga usah update
                const hasShift = [...row.querySelectorAll(".shift-select")]
                    .some(s => s.value !== "");

                if (!hasShift) return;

                try {
                    await post("{{ route('schedule.updateDivision', $plan->id) }}", {
                        user_id: userId,
                        division: division,
                    });
                } catch {
                    alert("Gagal update division.");
                }
            }

            function handleUserSelect(sel) {
                if (isPastMonth) return;

                const row = sel.closest("tr");
                const userId = sel.value;

                // Kalau user belum dipilih
                if (!userId) {
                    row.dataset.user = "";
                    row.querySelector(".division-select").disabled = true;
                    row.querySelectorAll(".shift-select").forEach(s => s.disabled = true);
                    return;
                }

                // Set employeeID ke row
                row.dataset.user = userId;

                // Enable division + shift selects
                row.querySelector(".division-select").disabled = false;

                row.querySelectorAll(".shift-select").forEach(s => {
                    s.disabled = false;
                });
            }

            function filterUserOptions(selectEl) {
                if (!selectEl) return;

                const usedSet = new Set(
                    [...document.querySelectorAll("tr[data-user]")]
                    .map(row => row.dataset.user)
                );

                [...selectEl.options].forEach(opt => {

                    // selalu tampilkan placeholder
                    if (opt.value === "") {
                        opt.hidden = false;
                        return;
                    }

                    // sembunyikan user yang sudah dipakai
                    if (usedSet.has(opt.value)) {
                        opt.hidden = true;
                    } else {
                        opt.hidden = false;
                    }
                });
            }

            document.addEventListener("DOMContentLoaded", () => {
                calculateTotals();
                updateCellColors();
                bindEvents();
            });
        </script>
    @endif
@endsection
