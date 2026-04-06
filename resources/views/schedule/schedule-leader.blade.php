@extends('layouts.app')

@section('styles')
    <style>
        /* =========================================
                                                                   1. MAGIC FIX "ANTI TUSUK SATE" (BORDER BLEED)
                                                                   ========================================= */
        .schedule-table {
            border-collapse: separate !important;
            border-spacing: 0;
            border-top: 1px solid #dee2e6;
        }

        .schedule-table th,
        .schedule-table td {
            border: none !important;
            border-right: 1px solid #dee2e6 !important;
            border-bottom: 1px solid #dee2e6 !important;
            background-clip: padding-box !important;
        }

        .schedule-table tr th:first-child,
        .schedule-table tr td:first-child {
            border-left: 1px solid #dee2e6 !important;
        }

        /* =========================================
                                                                   2. Z-INDEX & STICKY LOGIC
                                                                   ========================================= */
        .schedule-table thead th {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: var(--bs-primary);
            color: white;
        }

        /* Sticky Kolom 1 (Operator Name) */
        .sticky-col-left {
            position: sticky;
            left: 0;
            z-index: 1021;
            box-shadow: inset -2px 0 5px -2px rgba(0, 0, 0, 0.1);
        }

        tbody .sticky-col-left {
            background-color: #ffffff;
        }

        /* Ujung Kiri Atas */
        .schedule-table thead th.sticky-col-left {
            z-index: 1023 !important;
        }

        /* Footer Total */
        .schedule-table tfoot td {
            position: sticky;
            bottom: 0;
            z-index: 1020;
            background-color: var(--bs-dark);
            color: white;
        }

        /* Ujung Kiri Bawah */
        .schedule-table tfoot td.sticky-col-left {
            z-index: 1023 !important;
        }

        /* =========================================
                                                                   3. UI CELL (EXCEL MODE)
                                                                   ========================================= */
        .shift-select {
            min-width: 45px !important;
            padding: 4px 0 !important;
            text-align: center;
            border-radius: 0;
            cursor: pointer;
            font-weight: 600;
            background-color: transparent;

            /* Bantai panah dropdown */
            appearance: none !important;
            -webkit-appearance: none !important;
            background-image: none !important;
        }

        .shift-select:focus {
            background-color: var(--bs-primary-bg-subtle);
            box-shadow: inset 0 0 0 2px var(--bs-primary);
        }

        .shift-select:disabled {
            cursor: not-allowed;
            background-color: transparent;
        }

        /* Penanda Weekend */
        .bg-weekend {
            background-color: #fdfbf7 !important;
        }
    </style>
@endsection

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-calendar-check me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase">Schedule Control Operator</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid pb-2">

        {{-- SECTION 1: FILTER --}}
        <div class="card border-0 shadow-sm mb-2 rounded-3 shrink-0">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-auto">
                        <h6 class="fw-bold text-secondary mb-0 small text-uppercase">
                            <i class="bi bi-calendar3 me-1"></i> Operator Shift Plan
                        </h6>
                    </div>
                    <div class="col-auto">
                        <form action="{{ route('schedule.leader') }}" method="get" class="m-0 d-flex gap-2">
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white fw-bold text-muted border-end-0">Month:</span>
                                <input type="month" name="month"
                                    value="{{ $plan->year }}-{{ str_pad($plan->month, 2, '0', STR_PAD_LEFT) }}"
                                    class="form-control form-control-sm border-start-0 fw-bold text-primary"
                                    onchange="this.form.submit()" />
                            </div>

                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white fw-bold text-muted border-end-0"><i
                                        class="bi bi-person-badge"></i></span>
                                <select class="form-select form-select-sm border-start-0 fw-bold text-primary"
                                    name="leader" onchange="this.form.submit()">
                                    @foreach ($availableLeaders as $leader)
                                        <option value="{{ $leader->employeeID }}"
                                            {{ request('leader') == $leader->employeeID ? 'selected' : '' }}>
                                            {{ $leader->employeeID . ' - ' . $leader->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: MATRIX TABLE --}}
        <div class="card-fill position-relative bg-white rounded-3 shadow-sm border-0">

            <div class="table-responsive-wrapper p-0">
                {{-- Hapus table-bordered --}}
                <table class="table table-sm table-hover table-striped text-center align-middle text-nowrap schedule-table"
                    id="scheduleTable">

                    {{-- HEADER --}}
                    <thead class="table-primary">
                        <tr class="text-uppercase small">
                            <th class="sticky-col-left align-middle text-start ps-3" style="min-width: 180px;">Operator Name
                            </th>

                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $dateObj = \Carbon\Carbon::createFromDate($plan->year, $plan->month, $d);
                                    $isWeekend = $dateObj->isWeekend();
                                @endphp
                                <th style="min-width: 45px" class="{{ $isWeekend ? 'text-danger bg-warning-subtle' : '' }}">
                                    {{ $d }}<br>
                                    <span
                                        style="font-size: 0.65rem; font-weight: normal;">{{ $dateObj->format('D') }}</span>
                                </th>
                            @endfor
                        </tr>
                    </thead>

                    {{-- BODY --}}
                    <tbody class="small">
                        @forelse ($targets as $target)
                            <tr data-user="{{ $target['id'] }}">
                                {{-- Sticky Column 1 --}}
                                <td class="sticky-col-left text-start ps-3 fw-bold text-secondary">
                                    {{ $target['id'] . ' - ' . $target['name'] }}
                                </td>

                                {{-- Cells Tanggal --}}
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dateObj = \Carbon\Carbon::createFromDate($plan->year, $plan->month, $d);
                                        $dateStr = $dateObj->format('Y-m-d');
                                        $shift = $target['dates'][$dateStr] ?? '';

                                        // Disable logic
                                        $isOldDate = $isCurrentMonth && $dateStr < $today->format('Y-m-d');
                                        $disabled = $isPastMonth || $isOldDate ? 'disabled' : '';

                                        // Color logic
                                        $colorClass = $shift === 'L' ? 'bg-danger text-white' : '';
                                        $bgClass = $dateObj->isWeekend() && $shift !== 'L' ? 'bg-weekend' : '';
                                    @endphp

                                    <td class="p-0 {{ $bgClass }} position-relative">
                                        <select class="form-select form-select-sm shift-select {{ $colorClass }}"
                                            data-date="{{ $dateStr }}" {{ $disabled }}>
                                            <option value=""></option>
                                            @foreach (['1', '2', '3', 'L'] as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ (string) $shift === (string) $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 2 }}" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada data Operator untuk Leader ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- FOOTER TOTALS --}}
                    <tfoot>
                        <tr class="bg-dark text-white small">
                            <td class="sticky-col-left bg-dark text-white text-end pe-3 fw-bold text-uppercase"
                                style="letter-spacing: 1px;">
                                Total Shift Masuk
                            </td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                <td class="total-day fw-bold align-middle" data-day="{{ $d }}">0</td>
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- SECTION 3: ACTION BAR --}}
        <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Back
            </a>
            <div class="small text-muted d-none d-md-block">
                <i class="bi bi-info-circle me-1"></i> Auto-saving enabled. Max 10 Operators per day.
            </div>
        </div>

    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const planId = {{ $plan->id ?? 0 }};

        // --- UTILS ---
        function debounce(func, delay = 500) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => func.apply(this, args), delay);
            };
        }

        async function postData(url, data) {
            if (planId === 0) return {
                success: false
            };
            try {
                document.body.style.cursor = 'wait';
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(data)
                });
                if (!res.ok) throw new Error("HTTP Error");
                return await res.json();
            } catch (err) {
                console.error(err);
                return {
                    success: false
                };
            } finally {
                document.body.style.cursor = 'default';
            }
        }

        function updateCellVisuals(sel) {
            sel.className = "form-select form-select-sm shift-select border-0 shadow-none";
            if (sel.value === 'L') {
                sel.classList.add('bg-danger', 'text-white');
            }
        }

        // --- HANDLERS ---
        const saveShiftDebounced = debounce(async (userId, date, shift) => {
            const url = "{{ route('schedule.updateCell', ':id') }}".replace(':id', planId);
            await postData(url, {
                user_id: userId,
                date: date,
                shift: shift
            });
        }, 400);

        function handleShiftChange(sel) {
            const date = sel.dataset.date;
            const shift = sel.value;
            const row = sel.closest("tr");
            const userId = row.dataset.user;

            if (shift === '' || shift === 'L') {
                saveShiftWrapper(sel, userId);
                return;
            }

            // VALIDASI: Max 10 Operator per Hari
            const inputsOnDate = document.querySelectorAll(`.shift-select[data-date="${date}"]`);
            let count = 0;
            inputsOnDate.forEach(input => {
                if (input !== sel && ['1', '2', '3'].includes(input.value)) count++;
            });

            if (count >= 10) {
                alert("Waduh! Kuota harian habis. Max 10 Operator per hari.");
                sel.value = "";
                return;
            }

            saveShiftWrapper(sel, userId);
        }

        function saveShiftWrapper(sel, userId) {
            updateCellVisuals(sel);
            calculateTotals();
            saveShiftDebounced(userId, sel.dataset.date, sel.value);
        }

        function calculateTotals() {
            const totals = {};
            const holidayCount = {};
            const dateMap = {};

            document.querySelectorAll('.shift-select').forEach(sel => {
                const dateStr = sel.dataset.date;
                const day = parseInt(dateStr.split('-')[2]);

                if (!dateMap[day]) dateMap[day] = dateStr;

                if (['1', '2', '3'].includes(sel.value)) {
                    totals[day] = (totals[day] || 0) + 1;
                } else if (sel.value === 'L') {
                    holidayCount[day] = (holidayCount[day] || 0) + 1;
                }
            });

            document.querySelectorAll('.total-day').forEach(td => {
                const day = parseInt(td.dataset.day);
                const val = totals[day] || 0;
                const holidays = holidayCount[day] || 0;
                const dateStr = dateMap[day];

                td.classList.remove('bg-secondary', 'text-white');
                td.textContent = val;

                if (dateStr) {
                    const dateObj = new Date(dateStr);
                    const dayOfWeek = dateObj.getUTCDay();

                    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    const isManualHoliday = (val === 0 && holidays > 0);

                    if (isWeekend || isManualHoliday) {
                        td.textContent = "L";
                        td.classList.add('bg-secondary', 'text-white');
                    }
                }
            });
        }

        // ---------------- INIT ----------------
        document.addEventListener("DOMContentLoaded", () => {
            calculateTotals();
            document.querySelectorAll('.shift-select').forEach(sel => updateCellVisuals(sel));

            // Event Delegation for both Shift selects
            document.querySelector('#scheduleTable tbody').addEventListener('change', (e) => {
                if (e.target.classList.contains('shift-select')) {
                    handleShiftChange(e.target);
                }
            });
        });
    </script>
@endsection
