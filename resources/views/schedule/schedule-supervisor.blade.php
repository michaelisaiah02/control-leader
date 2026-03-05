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
    @php
        // Bikin gembok: Ambil akhir minggu dari hari ini (Default Carbon = Hari Minggu)
        $endOfCurrentWeek = \Carbon\Carbon::now()->endOfWeek();
    @endphp
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
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1060;">
        <div id="jsToast" class="toast align-items-center border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="toast-header">
                <i id="jsToastIcon" class="bi me-2"></i>
                <strong class="me-auto" id="jsToastTitle">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="jsToastBody">
                {{-- Pesan bakal diinject via JS --}}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // ---------------- UTILS ----------------
        function showToast(type, message) {
            const toastEl = document.getElementById('jsToast');
            const titleEl = document.getElementById('jsToastTitle');
            const bodyEl = document.getElementById('jsToastBody');
            const iconEl = document.getElementById('jsToastIcon');

            // Reset class warnanya biar ga numpuk
            toastEl.className = 'toast align-items-center border-0 text-bg-' + type;
            iconEl.className = 'bi me-2 text-' + type;

            // Setting UI berdasarkan tipe
            if (type === 'danger') {
                titleEl.textContent = 'Validasi Gagal';
                iconEl.classList.add('bi-x-square-fill');
                toastEl.classList.remove('text-bg-danger');
                toastEl.classList.add('bg-danger', 'text-white'); // Fix contrast Bootstrap
            } else if (type === 'warning') {
                titleEl.textContent = 'Peringatan';
                iconEl.classList.add('bi-exclamation-triangle-fill');
            } else if (type === 'success') {
                titleEl.textContent = 'Berhasil';
                iconEl.classList.add('bi-check-square-fill');
            }

            // Inject pesan
            bodyEl.innerHTML = message;

            // Panggil Bootstrap Toast API lalu tampilkan
            const bsToast = new bootstrap.Toast(toastEl, {
                delay: 3000
            }); // Auto hilang dlm 3 detik
            bsToast.show();
        }

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
                showToast('warning', "Satu Leader cuma boleh dicek 1x per minggu!");
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
                showToast('warning', "Maximal pengecekan 3 Leader per minggu tercapai!");
                sel.value = "";
                return;
            }

            saveAndCalculate(sel, userId);
        }

        function calculateTotals() {
            const totals = {};

            // Hitung jumlah cell biru per tanggal
            document.querySelectorAll('.schedule-cell').forEach(cell => {
                const day = parseInt(cell.dataset.day);
                if (cell.classList.contains('bg-primary')) {
                    totals[day] = (totals[day] || 0) + 1;
                }
            });

            // Update angka di footer
            document.querySelectorAll('.total-day').forEach(td => {
                const day = parseInt(td.dataset.day);
                const val = totals[day] || 0;
                td.textContent = val;
            });
        }

        let startSelection = null;

        document.querySelectorAll('.schedule-cell').forEach(cell => {
            cell.addEventListener('click', function() {
                if (this.dataset.locked === 'true') {
                    showToast('warning',
                        'Jadwal untuk minggu ini (dan sebelumnya) sudah <b>terkunci</b>. Anda hanya bisa mengatur jadwal untuk minggu depan.'
                        );

                    // Kalau dia lagi nyoba bikin rentang (udah klik 1) terus klik yg kekunci, batalin
                    if (startSelection) {
                        startSelection.cell.classList.remove('bg-info', 'text-white');
                        startSelection = null;
                    }
                    return; // Stop program eksekusi ke bawah
                }

                const userId = this.dataset.user;
                const currentDay = parseInt(this.dataset.day);
                const currentWeek = this.dataset.week;
                const row = this.closest('tr');

                // FITUR BARU: Hapus Jadwal per Minggu
                // Kalau user klik cell yang udah aktif (warna biru)
                if (this.classList.contains('bg-primary')) {
                    if (confirm('Mau hapus jadwal pengecekan di minggu ini?')) {
                        // Hapus warna biru cuma di minggu yang di-klik
                        row.querySelectorAll(`.schedule-cell[data-week="${currentWeek}"]`).forEach(c => {
                            c.classList.remove('bg-primary', 'text-white');
                            c.innerHTML = '';
                        });
                        kirimKeDatabase(userId, row); // Save kondisi terbaru
                    }
                    startSelection = null; // Reset state
                    return;
                }

                // KONDISI 1: Klik Pertama (Start Range)
                if (!startSelection || startSelection.userId !== userId) {
                    // Bersihin highlight 'temporary' (warna info)
                    document.querySelectorAll(`.schedule-cell.bg-info`).forEach(c => c.classList.remove(
                        'bg-info', 'text-white'));

                    startSelection = {
                        userId: userId,
                        day: currentDay,
                        week: currentWeek, // Simpen data minggunya
                        cell: this
                    };

                    this.classList.add('bg-info', 'text-white'); // Kasih warna biru muda sbg penanda
                    return;
                }

                // KONDISI 2: Klik Kedua (End Range)
                const endDay = currentDay;
                const endWeek = currentWeek;

                // VALIDASI SAKTI: Cek apakah masih di minggu yang sama!
                if (startSelection.week !== endWeek) {
                    showToast('warning',
                        'Rentang waktu pengecekan harus berada di dalam minggu yang sama!');
                    startSelection.cell.classList.remove('bg-info', 'text-white'); // Reset klik pertama
                    startSelection = null;
                    return;
                }

                const minDay = Math.min(startSelection.day, endDay);
                const maxDay = Math.max(startSelection.day, endDay);

                // --- [ START FIX ANTI BOLONG ] ---
                // 1. SAPU BERSIH: Hapus semua warna biru & info khusus di MINGGU INI aja
                // Biar sisa-sisa klik sebelumnya (kayak tgl 2,3,4) musnah dulu
                row.querySelectorAll(`.schedule-cell[data-week="${startSelection.week}"]`).forEach(c => {
                    c.classList.remove('bg-info', 'bg-primary', 'text-white');
                    c.innerHTML = '';
                });

                // 2. GAMBAR ULANG: Baru kita kasih warna biru solid berurutan dari minDay ke maxDay
                row.querySelectorAll(`.schedule-cell[data-week="${startSelection.week}"]`).forEach(c => {
                    const cellDay = parseInt(c.dataset.day);
                    if (cellDay >= minDay && cellDay <= maxDay) {
                        c.classList.add('bg-primary', 'text-white');
                        c.innerHTML = '<i class="bi bi-check-lg"></i>';
                    }
                });
                // --- [ END FIX ANTI BOLONG ] ---

                startSelection = null; // Reset state buat next klik

                // Execute save!
                kirimKeDatabase(userId, row);
            });
        });

        // Fungsi buat ngumpulin semua tanggal di 1 baris lalu kirim ke server
        function kirimKeDatabase(userId, row) {
            const datesToSave = [];

            // Kita ambil SEMUA cell yang nyala (warna biru) di baris leader ini
            row.querySelectorAll('.schedule-cell.bg-primary').forEach(c => {
                datesToSave.push(c.dataset.date);
            });

            // Panggil fungsi AJAX lo yang kemaren
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
                if (!data.success) showToast('danger', 'Gagal save ke database!');

            } catch (err) {
                console.error(err);
            } finally {
                document.body.style.cursor = 'default';
            }
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
