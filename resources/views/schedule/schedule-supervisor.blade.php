@extends('layouts.app')

@section('styles')
    <style>
        /* =========================================
               1. MAGIC FIX "ANTI TUSUK SATE" (BORDER BLEED)
               ========================================= */
        .schedule-table {
            border-collapse: separate !important;
            /* Wajib separate biar border gak nempel */
            border-spacing: 0;
            border-top: 1px solid #dee2e6;
            /* Bikin border luar manual */
        }

        /* Reset dan styling border untuk semua cell */
        .schedule-table th,
        .schedule-table td {
            border: none !important;
            /* Matikan border bawaan bootstrap */
            border-right: 1px solid #dee2e6 !important;
            border-bottom: 1px solid #dee2e6 !important;

            /* INI OBAT UTAMANYA: Mencegah background meluber ke bawah border */
            background-clip: padding-box !important;
        }

        /* Border paling kiri tabel */
        .schedule-table tr th:first-child,
        .schedule-table tr td:first-child {
            border-left: 1px solid #dee2e6 !important;
        }

        /* =========================================
               2. Z-INDEX & STICKY LOGIC
               ========================================= */

        /* Header Bulan (Atas) */
        .schedule-table thead th {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: var(--bs-primary);
            /* Wajib solid color */
            color: white;
        }

        /* Kolom Kiri (Nama Leader) */
        .sticky-col-left {
            position: sticky;
            left: 0;
            z-index: 1021;
            /* Lebih tinggi dari header biasa */
        }

        /* Pastikan background kolom nama solid putih (biar ga transparan pas discroll) */
        tbody .sticky-col-left {
            background-color: #ffffff;
        }

        /* Ujung Kiri Atas (Header "Leader Name") */
        .schedule-table thead th.sticky-col-left {
            z-index: 1023 !important;
            /* Paling Tinggi */
        }

        /* Footer Total (Bawah) */
        .schedule-table tfoot td {
            position: sticky;
            bottom: 0;
            z-index: 1020;
            background-color: var(--bs-dark);
            color: white;
        }

        /* Ujung Kiri Bawah (Label "Total Shift Masuk") */
        .schedule-table tfoot td.sticky-col-left {
            z-index: 1023 !important;
        }

        /* =========================================
               3. UI CELL (EXCEL MODE)
               ========================================= */
        .shift-select {
            min-width: 45px !important;
            padding: 4px 0 !important;
            /* Hapus padding chevron */
            text-align: center;
            border-radius: 0;
            cursor: pointer;
            font-weight: 600;
            background-color: transparent;

            /* Bantai panah dropdown bawaan browser & bootstrap */
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
        class="d-inline-flex align-items-center justify-content-center px-3 py-1 mt-1 mb-0 rounded-3 bg-white bg-opacity-10 border border-light text-white subtitle">
        <i class="bi bi-calendar-range me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase">Schedule Control Leader</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid layout-fixed pb-2">

        {{-- SECTION 1: FILTER (Compact Header) --}}
        <div class="card border-0 shadow-sm mb-2 rounded-3 shrink-0">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-auto">
                        <h6 class="fw-bold text-secondary mb-0 small text-uppercase">
                            <i class="bi bi-calendar3 me-1"></i> Working Shift Plan
                        </h6>
                    </div>
                    <div class="col-auto">
                        <form action="{{ route('schedule.index') }}" method="get" class="m-0">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white fw-bold text-muted border-end-0">Month:</span>
                                <input type="month" name="month" id="monthPicker"
                                    value="{{ $plan->year }}-{{ str_pad($plan->month, 2, '0', STR_PAD_LEFT) }}"
                                    class="form-control form-control-sm border-start-0 fw-bold text-primary"
                                    onchange="this.form.submit()" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: MATRIX TABLE --}}
        <div class="card-fill position-relative bg-white rounded-3 shadow-sm border-0">

            {{-- .table-responsive-wrapper bikin body area bisa scroll X dan Y --}}
            <div class="table-responsive-wrapper p-0">
                <table class="table table-hover table-sm text-center align-middle text-nowrap schedule-table"
                    id="scheduleTable">

                    {{-- HEADER (Sticky Top) --}}
                    <thead class="table-primary">
                        <tr class="text-uppercase small">
                            <th class="sticky-col-left align-middle text-start ps-3"
                                style="min-width: 220px; letter-spacing: 1px;">Leader Name</th>

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
                                    {{ $target['name'] }}
                                </td>

                                {{-- Cells Tanggal --}}
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dateObj = \Carbon\Carbon::createFromDate($plan->year, $plan->month, $d);
                                        $dateStr = $dateObj->format('Y-m-d');
                                        $weekNum = $dateObj->weekOfYear;
                                        $isWeekend = $dateObj->isWeekend();

                                        $shift = $target['dates'][$dateStr] ?? '';

                                        // Disable logic
                                        $isOldDate = $isCurrentMonth && $dateStr < $today->format('Y-m-d');
                                        $disabled = $isPastMonth || $isOldDate ? 'disabled' : '';

                                        // Color logic
                                        $colorClass = $shift === 'L' ? 'bg-danger text-white' : '';
                                        $bgClass = $isWeekend && $shift !== 'L' ? 'bg-weekend' : '';
                                    @endphp

                                    <td class="p-0 {{ $bgClass }} position-relative">
                                        <select
                                            class="form-select form-select-sm shift-select border-0 shadow-none {{ $colorClass }}"
                                            data-date="{{ $dateStr }}" data-week="{{ $weekNum }}"
                                            {{ $disabled }}>

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
                                <td colspan="{{ $daysInMonth + 1 }}" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                    Tidak ada data Leader yang ditemukan bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- FOOTER TOTALS (Sticky Bottom) --}}
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
        <div class="action-bar-static d-flex justify-content-between align-items-center px-2 mt-2">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
            </a>
            <div class="small text-muted d-none d-md-block">
                <i class="bi bi-info-circle me-1"></i> Auto-saving enabled. Max 1 shift/week per leader.
            </div>
        </div>

    </div>
    <x-toast />
@endsection

@section('scripts')
    {{-- JS 100% SAMA, cuma dibungkus rapi --}}
    <script type="module">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // ---------------- UTILS ----------------
        function debounce(func, timeout = 300) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    func.apply(this, args);
                }, timeout);
            };
        }

        async function postData(data) {
            try {
                // Tampilkan loading kecil di mouse/cursor atau toast (opsional)
                document.body.style.cursor = 'wait';

                const res = await fetch("{{ route('schedule.updateCell', $plan->id ?? 0) }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(data)
                });
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

        const saveShiftDebounced = debounce(async (userId, date, shift) => {
            await postData({
                user_id: userId,
                date: date,
                shift: shift
            });
        }, 500);

        function saveAndCalculate(sel, userId) {
            updateCellVisuals(sel);
            calculateTotals();
            saveShiftDebounced(userId, sel.dataset.date, sel.value);
        }

        // ---------------- HANDLERS ----------------
        function handleShiftChange(sel) {
            const row = sel.closest("tr");
            const userId = row.dataset.user;
            const week = sel.dataset.week;
            const shift = sel.value;

            if (shift === '' || shift === 'L') {
                saveAndCalculate(sel, userId);
                return;
            }

            // VALIDASI 2: 1x Seminggu Per Leader
            const rowInputsInWeek = row.querySelectorAll(`.shift-select[data-week="${week}"]`);
            let alreadyCheckedInWeek = false;

            rowInputsInWeek.forEach(input => {
                if (input !== sel && ['1', '2', '3'].includes(input.value)) {
                    alreadyCheckedInWeek = true;
                }
            });

            if (alreadyCheckedInWeek) {
                alert("Satu Leader cuma boleh dicek 1x per minggu!");
                sel.value = "";
                return;
            }

            // VALIDASI 3: Max 3 Leader Beda per Minggu
            const allRows = document.querySelectorAll("#scheduleTable tbody tr[data-user]");
            const leadersCheckedThisWeek = new Set();

            allRows.forEach(r => {
                const inputs = r.querySelectorAll(`.shift-select[data-week="${week}"]`);
                let hasCheck = false;
                inputs.forEach(i => {
                    if (i !== sel && ['1', '2', '3'].includes(i.value)) hasCheck = true;
                });
                if (hasCheck) leadersCheckedThisWeek.add(r.dataset.user);
            });

            if (!leadersCheckedThisWeek.has(userId) && leadersCheckedThisWeek.size >= 3) {
                alert("Maximal pengecekan 3 Leader per minggu tercapai!");
                sel.value = "";
                return;
            }

            saveAndCalculate(sel, userId);
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

            document.querySelector('#scheduleTable tbody').addEventListener('change', (e) => {
                if (e.target.classList.contains('shift-select')) {
                    handleShiftChange(e.target);
                }
            });
        });
    </script>
@endsection
