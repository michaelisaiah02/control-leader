@extends('layouts.app')

@section('styles')
    <style>
        /* Visual Drag & Drop Modern */
        .cursor-grab {
            cursor: grab;
        }

        .cursor-grabbing {
            cursor: grabbing !important;
        }

        /* Row saat sedang di-drag (melayang) */
        .sortable-drag {
            background-color: rgba(var(--bs-primary-rgb), 0.9) !important;
            color: white !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Placeholder area tempat row akan jatuh */
        .sortable-ghost {
            background-color: var(--bs-warning-bg-subtle) !important;
            border: 2px dashed var(--bs-warning);
            opacity: 0.5;
        }

        /* Handle Grip Visual */
        .drag-handle {
            color: #adb5bd;
            transition: color 0.2s;
        }

        tr:hover .drag-handle {
            color: var(--bs-primary);
            cursor: grab;
        }
    </style>
@endsection

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-list-check me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase">Question List</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid dashboard-container pb-2 pb-xxl-4 my-2">

        {{-- SECTION 1: FILTER (Compact) --}}
        <div class="card border-0 shadow-sm mb-2 rounded-3 shrink-0">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-auto">
                        <h6 class="fw-bold text-secondary mb-0 small text-uppercase">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </h6>
                    </div>
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 ps-3">
                                <i class="bi bi-box-seam text-muted"></i>
                            </span>
                            <select id="filterPackage"
                                class="form-select form-select-sm border-start-0 ps-2 bg-white fw-bold">
                                <option value="">-- Show All Packages --</option>
                                <option value="awal_shift">Awal Shift</option>
                                <option value="saat_bekerja">Saat Bekerja</option>
                                <option value="setelah_istirahat">Setelah Istirahat</option>
                                <option value="akhir_shift">Akhir Shift</option>
                                <option value="leader">Leader</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: TABLE CONTAINER (Greedy Height) --}}
        <div class="card-fill position-relative bg-white rounded-3 shadow-sm border-0">

            {{-- Loading Overlay --}}
            <div id="table-loader"
                class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none z-3 d-flex justify-content-center align-items-center">
                <div class="d-flex flex-column align-items-center">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <small class="text-muted fw-bold">Loading Data...</small>
                </div>
            </div>

            {{-- Wrapper ini akan di-replace oleh AJAX --}}
            {{-- Kita kasih ID biar JS tau mau inject kemana --}}
            <div id="questionTableWrapper" class="d-flex flex-column">
                @include('questions._table', ['questions' => $questions])
            </div>

        </div>

        {{-- SECTION 3: ACTION BAR --}}
        <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 me-2 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Back
            </a>
            <a href="{{ route('question.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Add Question
            </a>
        </div>
    </div>
    {{-- Modal Delete --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Hapus pertanyaan ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    {{-- Pastikan library Sortable diload --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

    <script type="module">
        document.addEventListener("DOMContentLoaded", () => {
            const filterPackage = document.querySelector('#filterPackage');
            const tableWrapper = document.querySelector('#questionTableWrapper');
            const loader = document.querySelector('#table-loader');

            // --- 1. SORTABLE LOGIC (Modular) ---
            function initSortable() {
                const tbody = document.getElementById('sortableBody');
                if (!tbody) return;

                // Cek apakah mode sorting aktif (cuma aktif kalau filter package dipilih)
                // Kita cek dari class handle yang ada di row pertama (kalau ada)
                const isSortableActive = tbody.querySelector('.drag-handle') !== null;

                if (!isSortableActive) return;

                // Destroy old instance if exists
                if (tbody._sortable) tbody._sortable.destroy();

                tbody._sortable = Sortable.create(tbody, {
                    animation: 150,
                    handle: '.drag-handle', // Drag cuma bisa lewat icon grip
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    chosenClass: 'sortable-chosen',
                    forceFallback: true, // Biar cursor grabbing jalan lancar di semua browser

                    onStart: function() {
                        document.body.classList.add('cursor-grabbing');
                    },
                    onEnd: function(evt) {
                        const rows = document.querySelectorAll('#sortableBody tr');
                        let payload = [];

                        rows.forEach((row, index) => {
                            // Karena ga ada pagination, index 0 ya pasti urutan ke-1
                            // Index 20 ya urutan ke-21. Gak bakal ada duplikat.
                            let newOrder = index + 1;

                            // 1. Update Visual No
                            let cellNo = row.querySelector('td:first-child span');
                            if (cellNo) cellNo.textContent = newOrder;

                            // 2. Update Visual Hidden Order (opsional)
                            let cellDisplay = row.querySelector('.display-order-val');
                            if (cellDisplay) cellDisplay.textContent = newOrder;

                            payload.push({
                                id: row.getAttribute('data-id'),
                                display_order: newOrder
                            });
                        });

                        saveOrderToBackend(payload);
                    }
                });
            }

            function updateVisualOrder() {
                const tbody = document.getElementById('sortableBody');
                const rows = tbody.querySelectorAll('tr');

                // Ambil start index dari data attribute (buat handle pagination)
                // Kalau gak ada, default 0
                let startIndex = parseInt(tbody.getAttribute('data-start-index')) || 0;

                rows.forEach((row, index) => {
                    // 1. UPDATE KOLOM NO (Biar visualnya urut 1, 2, 3 lagi)
                    const numberCell = row.querySelector('.row-number');
                    if (numberCell) {
                        // Index loop dimulai dari 0, jadi +1. Ditambah start index pagination.
                        numberCell.textContent = startIndex + index + 1;
                    }

                    // 2. UPDATE KOLOM ORDER (Kolom ke-3 di screenshot lo)
                    const displayCell = row.querySelector('.display-order-val');
                    if (displayCell) {
                        displayCell.textContent = index + 1;
                    }
                });
            }

            function saveOrderToBackend(orderData) {
                fetch("{{ route('question.updateOrder') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            order: orderData,
                            package: filterPackage.value
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Optional: Toast success
                            console.log('Order saved');
                        } else {
                            alert('Failed to save order!');
                        }
                    })
                    .catch(err => console.error(err));
            }

            // --- 2. AJAX LOAD LOGIC ---
            function loadQuestions(url) {
                loader.classList.remove('d-none'); // Show loader

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Replace HTML
                        tableWrapper.innerHTML = data.html;

                        // Re-attach Events
                        attachPaginationEvents();
                        initSortable();
                    })
                    .catch(err => alert('Failed to load data'))
                    .finally(() => loader.classList.add('d-none')); // Hide loader
            }

            // --- 3. EVENT LISTENERS ---
            filterPackage.addEventListener('change', () => {
                const pkg = filterPackage.value;
                let url = `{{ route('question.index') }}`;
                if (pkg) url += `?package=${encodeURIComponent(pkg)}`;
                loadQuestions(url);
            });

            function attachPaginationEvents() {
                // Gunakan event delegation atau attach ulang
                document.querySelectorAll('#questionTableWrapper .pagination a').forEach(link => {
                    link.addEventListener('click', e => {
                        e.preventDefault();
                        loadQuestions(e.target.getAttribute('href'));
                    });
                });
            }

            $('#deleteModal').on('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const questionId = button.closest('tr').getAttribute('data-id');
                const form = document.getElementById('deleteForm');
                form.action = `{{ url('question') }}/${questionId}`;
            });

            // Init First Load
            initSortable();
            attachPaginationEvents();
        });
    </script>
@endsection
