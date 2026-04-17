@extends('layouts.app')

@section('styles')
    <style>
        /* ========================================= */
        /* KANBAN BOARD STYLES                       */
        /* ========================================= */
        .kanban-wrapper {
            /* Biar bisa scroll horizontal kalau di layar kecil */
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 0.75rem;

            /* Responsive Touch & Snapping buat Mobile */
            scroll-behavior: smooth;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            display: flex;
            gap: 1rem;
        }

        /* Custom Scrollbar biar elegan */
        .kanban-wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .kanban-wrapper::-webkit-scrollbar-track {
            background: #f1f3f5;
            border-radius: 10px;
        }

        .kanban-wrapper::-webkit-scrollbar-thumb {
            background-color: var(--bs-gray-400);
            border-radius: 10px;
        }

        .kanban-wrapper::-webkit-scrollbar-thumb:hover {
            background-color: var(--bs-gray-500);
        }

        .kanban-col {
            /* Lebar proporsional: min 260px, max seadanya layar */
            flex: 1 0 260px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            /* Snap per kolom pas di-swipe di HP */
            scroll-snap-align: start;
        }

        .week-header {
            background-color: var(--bs-gray-200);
            border: 2px solid var(--bs-gray-300);
            border-radius: 0.5rem;
            color: var(--bs-dark);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .leader-card {
            background-color: #fef08a;
            /* Kuning terang */
            border: 2px solid #eab308;
            border-radius: 0.5rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--bs-dark);
        }

        .leader-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-delete-card {
            background-color: transparent;
            border: 1px solid transparent;
            font-weight: 900;
            line-height: 1;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            color: var(--bs-dark);
            opacity: 0.5;
            transition: all 0.2s;
        }

        .leader-card:hover .btn-delete-card {
            opacity: 1;
            background-color: rgba(255, 255, 255, 0.5);
            border-color: #eab308;
        }

        .btn-delete-card:hover {
            background-color: var(--bs-danger) !important;
            color: white !important;
            border-color: var(--bs-danger) !important;
            opacity: 1;
        }

        .btn-add-leader {
            background-color: var(--bs-primary);
            color: white;
            font-weight: bold;
            border-radius: 0.5rem;
            border: none;
            padding: 0.6rem;
            transition: all 0.2s;
        }

        .btn-add-leader:hover {
            background-color: var(--bs-secondary);
            color: white;
            transform: translateY(-1px);
        }

        /* Styling buat label info di pojok bawah */
        .info-panel {
            background-color: var(--bs-warning-bg-subtle);
            border: 2px dashed var(--bs-warning);
            border-radius: 0.5rem;
            font-weight: 600;
            color: var(--bs-dark);
        }

        /* Layout perbaikan buat mobile biar ngga nempel action bar */
        @media (max-width: 576px) {
            .main-content-wrapper {
                margin-bottom: 400px !important;
            }
        }

        @media (min-width: 1200px) {
            .main-content-wrapper {
                margin-bottom: 110px !important;
            }
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
    <div class="container-fluid dashboard-container pb-2 pb-lg-3 pb-xxl-4 mt-2 main-content-wrapper">

        {{-- SECTION 1: FILTER HEADER --}}
        <div class="card border-0 shadow-sm rounded-3 mb-2">
            <div class="card-body p-2">
                <div class="row g-3 align-items-center justify-content-between">
                    <div class="col-12 col-md-auto text-center text-md-start">
                        <h6 class="fw-bold text-secondary mb-0 small text-uppercase">
                            <i class="bi bi-kanban me-1"></i> Control Leader Plan
                        </h6>
                    </div>
                    <div class="col-12 col-md-auto">
                        <form action="{{ route('schedule.index') }}" method="get" class="m-0">
                            <div class="input-group input-group-sm justify-content-center justify-content-md-end">
                                <span class="input-group-text bg-white fw-bold text-muted border-end-0">Month:</span>
                                <input type="month" name="month" id="monthPicker"
                                    value="{{ $plan->year }}-{{ str_pad($plan->month, 2, '0', STR_PAD_LEFT) }}"
                                    class="form-control form-control-sm border-start-0 fw-bold text-primary"
                                    style="max-width: 150px;" onchange="this.form.submit()" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: KANBAN BOARD --}}
        <div class="card position-relative bg-white rounded-3 shadow-sm p-2 mb-2 border-0">
            <div class="kanban-wrapper">

                @foreach ($weeksData as $weekNum => $weekData)
                    @php
                        // LOGIKA KUNCIAN FRONT-END
                        $currentDay = \Carbon\Carbon::now()->day;
                        $currentWeekLimit = ceil($currentDay / 7);
                        $isLocked = $isPastMonth || ($isCurrentMonth && $weekNum <= $currentWeekLimit);

                        // Efek visual kalau dikunci
                        $lockedClass = $isLocked ? 'opacity-75 bg-light' : '';
                    @endphp

                    <div class="kanban-col rounded-3 {{ $lockedClass }} p-2">
                        {{-- Week Header --}}
                        <div class="week-header text-center p-2 mb-2 shadow-sm d-flex flex-column align-items-center">
                            <h6 class="fw-bold mb-0 d-flex align-items-center gap-1">
                                Week {{ $weekNum }}
                                @if ($isLocked)
                                    <i class="bi bi-lock-fill text-danger fs-6 ms-1"></i>
                                @endif
                            </h6>
                            <small class="fw-bold text-secondary mt-1" style="font-size: 0.75rem;">Tanggal
                                {{ $weekData['label'] }}</small>
                        </div>

                        {{-- Cards Container --}}
                        <div class="leader-list-container d-flex flex-column gap-2" id="week-{{ $weekNum }}">
                            @foreach ($weekData['leaders'] as $leaderId => $leader)
                                <div class="leader-card d-flex justify-content-between align-items-center p-2 shadow-sm">
                                    <span class="small fw-bold text-truncate pe-2"
                                        title="{{ $leaderId }} - {{ $leader['name'] }}">
                                        {{ $leaderId }} - {{ $leader['name'] }}
                                    </span>

                                    @if (!$isLocked)
                                        <button class="btn btn-delete-card remove-leader-btn flex-shrink-0"
                                            data-user="{{ $leaderId }}" data-week="{{ $weekNum }}"
                                            data-name="{{ $leader['name'] }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Tombol Add --}}
                        @if (!$isLocked && count($weekData['leaders']) < 3)
                            <button class="btn btn-add-leader w-100 mt-2 shadow-sm add-leader-btn" data-bs-toggle="modal"
                                data-bs-target="#addLeaderModal" data-week="{{ $weekNum }}">
                                <i class="bi bi-plus-lg me-1"></i> Add Leader
                            </button>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>

        {{-- SECTION 3: BOTTOM AREA (Totals & Info) --}}
        <div class="row g-3">
            {{-- Totals --}}
            <div class="col-12 col-xl-8">
                <div class="card border-0 rounded-3 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div
                            class="d-inline-flex align-items-center bg-secondary text-white px-3 py-1 rounded-3 fw-bold mb-3">
                            <i class="bi bi-bar-chart-fill me-2"></i> Total Pengecekan
                        </div>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 small">
                            @foreach ($leaderTotals as $id => $data)
                                <div class="col d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <span class="text-dark fw-bold text-truncate pe-2">{{ $id }} -
                                        {{ $data['name'] }}</span>
                                    <div class="d-flex align-items-center shrink-0">
                                        <span class="badge bg-secondary fs-6 rounded-pill px-3">{{ $data['count'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Panel --}}
            <div class="col-12 col-xl-4">
                <div class="info-panel p-3 h-100 d-flex flex-column justify-content-center shadow-sm">
                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perhatian:
                    </h6>
                    <ul class="mb-0 ps-3 small text-dark">
                        <li class="mb-1">Standar pengecekan leader <b>hanya seminggu sekali</b>.</li>
                        <li>Maksimal pengecekan <b>3 leader / minggu</b>.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    {{-- ACTION BAR (Footer) --}}
    <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-2 d-flex justify-content-between align-items-center"
        style="z-index: 1030;">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> <span class="d-none d-sm-inline">Back</span>
        </a>
        <div class="small text-muted d-none d-md-block fw-bold">
            <i class="bi bi-check-circle-fill text-success me-1"></i> Data tersimpan secara otomatis
        </div>
        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold disabled shadow-sm" style="opacity: 0.8">
            <i class="bi bi-cloud-check me-1"></i> Saved
        </button>
    </div>

    {{-- ========================================= --}}
    {{-- MODALS                                    --}}
    {{-- ========================================= --}}

    {{-- MODAL ADD LEADER --}}
    <div class="modal fade" id="addLeaderModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 mt-2">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Add Leader <span id="modalWeekTitle"
                            class="badge bg-primary ms-2 rounded-pill">Week X</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-secondary px-4 py-3">
                    <form id="formAddLeader">
                        <input type="hidden" id="selectedWeek" name="week">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">Pilih Leader</label>
                            <select class="form-select form-select-lg border-2 shadow-sm fs-6" id="selectLeader"
                                name="user_id">
                                <option value="">-- Silahkan Pilih Leader --</option>
                                @foreach ($leaders as $leader)
                                    <option value="{{ $leader->employeeID }}">{{ $leader->employeeID }} -
                                        {{ $leader->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                        id="saveLeaderBtn">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DELETE CONFIRMATION --}}
    <div class="modal fade" id="deleteScheduleModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 mt-2">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-trash-fill me-2"></i>Hapus Jadwal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-secondary px-4 py-3">
                    Apakah Anda yakin ingin menghapus jadwal <b class="text-dark" id="deleteLeaderName">Nama</b> di <b
                        class="text-dark" id="deleteWeekNum">Week X</b>?<br><br>
                    <span class="text-dark fw-bold">Data akan langsung dihapus dari sistem.</span>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
                        id="confirmDeleteBtn">Ya, Hapus</button>
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
                    <div class="toast-body fw-bold text-white">
                        <i class="bi ${icon} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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

        // ---------------- KANBAN LOGIC ----------------
        let targetDelete = null;

        $(document).ready(function() {

            // 1. BUKA MODAL ADD LEADER & FILTER DROPDOWN
            $('.add-leader-btn').on('click', function() {
                const week = $(this).data('week');
                $('#selectedWeek').val(week);
                $('#modalWeekTitle').text('Week ' + week);
                $('#selectLeader').val('');

                const existingLeaders = [];
                $(`#week-${week} .remove-leader-btn`).each(function() {
                    existingLeaders.push($(this).data('user').toString());
                });

                $('#selectLeader option').each(function() {
                    const optionVal = $(this).val();
                    if (!optionVal) return;

                    if (existingLeaders.includes(optionVal)) {
                        $(this).prop('disabled', true).hide();
                    } else {
                        $(this).prop('disabled', false).show();
                    }
                });
            });

            // 2. SAVE LEADER (AJAX POST)
            $('#saveLeaderBtn').on('click', async function() {
                const userId = $('#selectLeader').val();
                const week = $('#selectedWeek').val();

                if (!userId) {
                    showToast('warning', 'Pilih leader terlebih dahulu!');
                    return;
                }

                const currentLeadersInWeek = $(`#week-${week} .leader-card`).length;
                const isLeaderExist = $(`#week-${week} .remove-leader-btn[data-user="${userId}"]`)
                    .length > 0;

                if (isLeaderExist) {
                    showToast('warning', 'Leader ini sudah dijadwalkan di minggu tersebut!');
                    return;
                }

                if (currentLeadersInWeek >= 3) {
                    showToast('danger', 'Maksimal 3 Leader per minggu sudah tercapai!');
                    return;
                }

                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                try {
                    const res = await fetch(
                        "{{ route('schedule.addWeeklyLeader', $plan->id ?? 0) }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                week: parseInt(week)
                            })
                        });

                    const data = await res.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        showToast('danger', 'Gagal menyimpan: ' + (data.message || 'Error Database'));
                        btn.prop('disabled', false).html(originalText);
                    }
                } catch (err) {
                    console.error(err);
                    showToast('danger', 'Koneksi terputus. Coba lagi.');
                    btn.prop('disabled', false).html(originalText);
                }
            });

            // 3. BUKA MODAL HAPUS
            $(document).on('click', '.remove-leader-btn', function() {
                targetDelete = {
                    userId: $(this).data('user'),
                    week: $(this).data('week'),
                    name: $(this).data('name')
                };

                $('#deleteLeaderName').text(targetDelete.name);
                $('#deleteWeekNum').text('Week ' + targetDelete.week);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteScheduleModal')).show();
            });

            // 4. KONFIRMASI HAPUS (AJAX POST)
            $('#confirmDeleteBtn').on('click', async function() {
                if (!targetDelete) return;

                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...');

                try {
                    const res = await fetch(
                        "{{ route('schedule.removeWeeklyLeader', $plan->id ?? 0) }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                user_id: targetDelete.userId,
                                week: targetDelete.week
                            })
                        });

                    const data = await res.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        showToast('danger', 'Gagal menghapus jadwal.');
                        btn.prop('disabled', false).html(originalText);
                    }
                } catch (err) {
                    showToast('danger', 'Koneksi terputus.');
                    btn.prop('disabled', false).html(originalText);
                }
            });

        });
    </script>
@endsection
