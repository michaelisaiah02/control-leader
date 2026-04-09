@extends('layouts.app')

@section('styles')
    <style>
        /* ========================================= */
        /* 1. MAGIC FIX "ANTI TUSUK SATE" (BORDER BLEED) */
        /* ========================================= */
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

        /* ========================================= */
        /* 2. Z-INDEX & STICKY LOGIC */
        /* ========================================= */

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

        /* ========================================= */
        /* 3. UI CELL (EXCEL MODE) */
        /* ========================================= */
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

        .custom-tooltip {
            --bs-tooltip-bg: var(--bs-primary);
            --bs-tooltip-color: var(--bs-white);
        }
    </style>
@endsection

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-calendar-range me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase text-truncate">Schedule Control Leader</span>
    </div>
@endpush

@section('content')
    @php
        // Bikin gembok: Ambil akhir minggu dari hari ini (Default Carbon = Hari Minggu)
        $endOfCurrentWeek = \Carbon\Carbon::now()->endOfWeek();
    @endphp
    <div class="container-fluid dashboard-container pb-2 pb-lg-3 pb-xxl-4 my-2">

        {{-- SECTION 1: FILTER (Compact Header) --}}
        <div class="card border-0 shadow-sm mb-2 rounded-3 shrink-0">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-auto">
                        <h6 class="fw-bold text-secondary mb-0 small text-uppercase">
                            <i class="bi bi-calendar3 me-1"></i> Control Leader Plan
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
                                    {{ $target['id'] . ' - ' . $target['name'] }}
                                </td>

                                {{-- Cells Tanggal --}}
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dateObj = \Carbon\Carbon::createFromDate($plan->year, $plan->month, $d);
                                        $dateStr = $dateObj->format('Y-m-d');
                                        $weekNum = $dateObj->weekOfYear;
                                        $isWeekend = $dateObj->isWeekend();

                                        // LOGIKA KUNCIAN: Kalau tanggal ini <= akhir minggu ini, KUNCI!
                                        $isLocked = $dateObj->lte($endOfCurrentWeek);

                                        $isSelected =
                                            isset($target['dates'][$dateStr]) && $target['dates'][$dateStr] === 'Y';
                                        $bgClass = $isWeekend ? 'bg-weekend' : '';
                                        $activeClass = $isSelected ? 'bg-primary text-white' : '';

                                        // Kasih efek visual redup dikit kalau kekunci biar user paham
                                        $lockedOpacity = $isLocked ? 'opacity: 0.6;' : '';
                                        $cursorStyle = $isLocked ? 'cursor: not-allowed;' : 'cursor: pointer;';
                                    @endphp

                                    <td class="p-0 {{ $bgClass }} position-relative border-right border-bottom">
                                        <div class="schedule-cell w-100 h-100 d-flex align-items-center justify-content-center {{ $activeClass }}"
                                            data-user="{{ $target['id'] }}" data-date="{{ $dateStr }}"
                                            data-day="{{ $d }}" data-week="{{ $weekNum }}"
                                            data-locked="{{ $isLocked ? 'true' : 'false' }}" {{-- TEMPEL STATUS GEMBOK DISINI --}}
                                            style="min-height: 35px; {{ $cursorStyle }} {{ $lockedOpacity }}">
                                            {!! $isSelected ? '<i class="bi bi-check-lg"></i>' : '' !!}
                                        </div>
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
        <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
            </a>
            <div class="small text-muted d-none d-md-block">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                    data-bs-custom-class="custom-tooltip"
                    data-bs-title="Klik tgl awal & tgl akhir untuk rentang.
                Klik 2x tgl yg sama untuk 1 hari. Maks 3 leader per minggu.">
                    <i class="bi bi-info-circle me-1"></i>Hint
                </button>
            </div>
        </div>

    </div>

    {{-- MODAL KONFIRMASI HAPUS JADWAL --}}
    <div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 mt-2">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-trash-fill me-2"></i>Hapus Jadwal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-secondary px-4 py-3">
                    Apakah Anda yakin ingin menghapus jadwal pengecekan untuk <b class="text-dark"
                        id="deleteRangeText">tanggal ini</b>?<br><br>
                    <span class="text-dark fw-bold">Jadwal pada hari tersebut akan dikosongkan.</span>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
                        id="confirmDeleteScheduleBtn">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // ---------------- UTILS (DYNAMIC TOAST) ----------------
        function showToast(type, message) {
            let icon = 'bi-info-circle-fill';
            if (type === 'danger') icon = 'bi-x-circle-fill';
            if (type === 'warning') icon = 'bi-exclamation-triangle-fill';
            if (type === 'success') icon = 'bi-check-circle-fill';

            const toastHtml = `
            <div class="toast align-items-center text-bg-${type} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold text-primary">
                        <i class="bi ${icon} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-primary me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

            let $container = $('.toast-container.js-toast-wrap');
            if ($container.length === 0) {
                $container = $(
                    '<div class="toast-container js-toast-wrap position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1060;"></div>'
                );
                $('body').append($container);
            }

            const $toast = $(toastHtml).appendTo($container);
            const toastInstance = new bootstrap.Toast($toast[0], {
                delay: 3000
            });
            toastInstance.show();
            $toast.on('hidden.bs.toast', () => $toast.remove());
        }

        // ---------------- HITUNG TOTAL ----------------
        function calculateTotals() {
            const totals = {};
            document.querySelectorAll('.schedule-cell').forEach(cell => {
                const day = parseInt(cell.dataset.day);
                if (cell.classList.contains('bg-primary')) {
                    totals[day] = (totals[day] || 0) + 1;
                }
            });

            document.querySelectorAll('.total-day').forEach(td => {
                const day = parseInt(td.dataset.day);
                td.textContent = totals[day] || 0;
            });
        }

        // ---------------- LOGIC UI SELECTION ----------------
        let startSelection = null;
        let deleteTarget = null; // Nyimpen data buat Modal

        // Fungsi buat ngereset warna biru muda (Klik 1) kalau batal
        function resetSelection() {
            if (startSelection) {
                startSelection.cell.classList.remove('bg-info', 'text-white');
                if (startSelection.isOriginallyActive) {
                    startSelection.cell.classList.add('bg-primary', 'text-white');
                } else {
                    startSelection.cell.innerHTML = '';
                }
                startSelection = null;
            }
        }

        document.querySelectorAll('.schedule-cell').forEach(cell => {
            cell.addEventListener('click', function() {

                // 1. CEK GEMBOK
                if (this.dataset.locked === 'true') {
                    showToast('warning', 'Jadwal untuk minggu ini sudah <b>terkunci</b>.');
                    resetSelection();
                    return;
                }

                const userId = this.dataset.user;
                const currentDay = parseInt(this.dataset.day);
                const currentWeek = this.dataset.week;
                const row = this.closest('tr');

                // 2. KONDISI: KLIK 1 (Start Range)
                if (!startSelection || startSelection.userId !== userId) {
                    resetSelection();

                    // Catat apakah kotak ini aslinya udah biru atau kosong
                    const isOriginallyActive = this.classList.contains('bg-primary');

                    startSelection = {
                        userId,
                        day: currentDay,
                        week: currentWeek,
                        cell: this,
                        isOriginallyActive
                    };

                    // Kasih warna biru muda sbg penanda
                    this.classList.remove('bg-primary');
                    this.classList.add('bg-info', 'text-white');
                    return;
                }

                // 3. KONDISI: KLIK 2 (End Range)
                if (startSelection.week !== currentWeek) {
                    showToast('warning', 'Rentang waktu harus di dalam minggu yang sama!');
                    resetSelection();
                    return;
                }

                const minDay = Math.min(startSelection.day, currentDay);
                const maxDay = Math.max(startSelection.day, currentDay);

                // --- CABANG LOGIKA: ADD atau DELETE? ---

                if (startSelection.isOriginallyActive) {
                    // Kalau klik 1 di tempat BIRU -> Berarti mau HAPUS! Panggil Modal.
                    deleteTarget = {
                        userId,
                        row,
                        minDay,
                        maxDay,
                        week: currentWeek
                    };

                    const dayText = minDay === maxDay ? `tanggal ${minDay}` :
                        `tanggal ${minDay} sampai ${maxDay}`;
                    document.getElementById('deleteRangeText').innerText = dayText;

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteScheduleModal'))
                        .show();
                } else {
                    // Kalau klik 1 di tempat KOSONG -> Berarti mau NAMBAH!

                    // Validasi Sakti: Maksimal 3 Leader
                    const allRows = document.querySelectorAll("#scheduleTable tbody tr[data-user]");
                    const leadersWithSchedule = new Set();
                    allRows.forEach(r => {
                        const hasSchedule = r.querySelector(
                            `.schedule-cell.bg-primary[data-week="${currentWeek}"]`);
                        if (hasSchedule) leadersWithSchedule.add(r.dataset.user);
                    });

                    if (!leadersWithSchedule.has(userId) && leadersWithSchedule.size >= 3) {
                        showToast('warning',
                            'Maksimal pengecekan 3 Leader berbeda per minggu sudah tercapai!');
                        resetSelection();
                        return;
                    }

                    // Terapkan penambahan (Nggak menghapus data di luar range)
                    row.querySelectorAll(`.schedule-cell[data-week="${currentWeek}"]`).forEach(c => {
                        const cellDay = parseInt(c.dataset.day);
                        if (cellDay >= minDay && cellDay <= maxDay) {
                            c.classList.remove('bg-info');
                            c.classList.add('bg-primary', 'text-white');
                            c.innerHTML = '<i class="bi bi-check-lg"></i>';
                        }
                    });

                    startSelection = null;
                    kirimKeDatabase(userId, row);
                }
            });
        });

        // ---------------- MODAL HAPUS JADWAL ----------------
        $('#confirmDeleteScheduleBtn').on('click', function() {
            if (!deleteTarget) return;

            const btn = $(this);
            const originalText = btn.html();
            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...');

            // Sapu bersih jadwal di range yang dipilih aja
            deleteTarget.row.querySelectorAll(`.schedule-cell[data-week="${deleteTarget.week}"]`).forEach(c => {
                const cellDay = parseInt(c.dataset.day);
                if (cellDay >= deleteTarget.minDay && cellDay <= deleteTarget.maxDay) {
                    c.classList.remove('bg-primary', 'bg-info', 'text-white');
                    c.innerHTML = '';
                }
            });

            kirimKeDatabase(deleteTarget.userId, deleteTarget.row);

            // Bersihin State
            startSelection = null;
            bootstrap.Modal.getInstance(document.getElementById('deleteScheduleModal')).hide();

            btn.prop('disabled', false).html(originalText);
            deleteTarget = null;
        });

        // Reset Klik 1 (Biru muda) kalau Modal Cancel ditutup
        document.getElementById('deleteScheduleModal').addEventListener('hidden.bs.modal', function() {
            resetSelection();
            deleteTarget = null;
        });

        // ---------------- AJAX SUBMIT ----------------
        function kirimKeDatabase(userId, row) {
            const datesToSave = [];
            row.querySelectorAll('.schedule-cell.bg-primary').forEach(c => {
                datesToSave.push(c.dataset.date);
            });

            saveRangeToDatabase(userId, datesToSave);
            calculateTotals();
        }

        async function saveRangeToDatabase(userId, dates) {
            try {
                document.body.style.cursor = 'wait';
                const res = await fetch("{{ route('schedule.updateRange', $plan->id ?? 0) }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        dates: dates
                    })
                });

                const data = await res.json();
                if (!data.success) showToast('danger', 'Gagal menyimpan ke database!');

            } catch (err) {
                console.error(err);
                showToast('danger', 'Terjadi kesalahan koneksi server.');
            } finally {
                document.body.style.cursor = 'default';
            }
        }

        // ---------------- INIT ----------------
        document.addEventListener("DOMContentLoaded", () => {
            calculateTotals();
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl))
        });
    </script>
@endsection
