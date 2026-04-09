@extends('layouts.app')

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-people-fill me-2"></i>
        <span class="fw-bold text-uppercase">Operator Management</span>
    </div>
@endpush

@section('styles')
    <style>
        .selectize-dropdown .optgroup-header {
            text-align: center;
            font-weight: bold;
            font-size: 0.9em;
            /* Opsional: sedikit lebih kecil biar beda sama opsi */
            background-color: #f8f9fa;
            /* Opsional: kasih warna background abu-abu terang */
            color: #6c757d;
        }

        /* Menyesuaikan Selectize agar ukurannya setara dengan form-select-sm */
        .selectize-input {
            min-height: calc(1.5em + .5rem + 2px) !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            border-radius: 0.25rem !important;
            display: flex !important;
            align-items: center;
        }

        /* Memperbaiki posisi text placeholder */
        .selectize-input>input {
            font-size: 0.875rem !important;
        }
    </style>
@endsection

@section('content')
    {{-- Layout Fixed: Halaman Gak Bisa Scroll, Cuma Tabel yang Scroll --}}
    <div class="container-fluid dashboard-container pb-2 pb-lg-3 pb-xxl-4 my-2">

        {{-- SECTION 1: FILTER (Compact) --}}
        <div class="card border-0 shadow-sm mb-2 rounded-3 shrink-0">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center">

                    {{-- Leader Filter --}}
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 fw-bold small text-muted text-uppercase"
                                style="width: 70px;">Leader</span>
                            <select id="leader" class="form-select form-select-sm border-start-0 bg-light fw-bold">
                                <option value="" selected>All Leaders</option>
                                @foreach ($leaders as $leader)
                                    <option value="{{ $leader->employeeID }}"
                                        data-department="{{ $leader->department->name ?? '-' }}">
                                        {{ $leader->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Department Info --}}
                    <div class="col-12 col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 fw-bold small text-muted text-uppercase"
                                style="width: 60px;">Dept</span>
                            <input type="text" class="form-control form-control-sm border-start-0 bg-light text-muted"
                                id="department" placeholder="-" readonly>
                        </div>
                    </div>

                    {{-- Search Input --}}
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="search" class="form-control form-control-sm border-start-0 ps-2"
                                id="search-operator" placeholder="Search Operator..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: TABLE (Card Fill + Single Table Sticky) --}}
        <div class="card-fill position-relative bg-white rounded-3 shadow-sm border-0">

            {{-- Loading Overlay --}}
            <div id="table-loader"
                class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none z-3 d-flex justify-content-center align-items-center">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>
            {{-- Wrapper Scroll --}}
            <div class="table-responsive-wrapper table-responsive">
                {{-- TABEL MENYATU (Header & Body) + Class .table-sticky-header --}}
                <table class="table table-sm table-hover table-striped mb-0 table-sticky-header" id="operator-table">
                    <thead class="table-primary small text-uppercase fw-bold text-center">
                        <tr>
                            {{-- Lebar kolom otomatis sinkron karena satu tabel --}}
                            <th>#</th>
                            <th>ID</th>
                            <th class="text-start">Name</th>
                            <th>Division</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="operator-table-body" class="small align-middle">
                        {{-- Data injected by AJAX --}}
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="card-footer bg-white border-top p-1 shrink-0">
                <div id="pagination-links" class="d-flex justify-content-center justify-content-md-end small"></div>
            </div>
        </div>

        {{-- SECTION 3: ACTION BAR --}}
        <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Back
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" id="btn-add-operator"
                data-bs-toggle="modal" data-bs-target="#operatorModal">
                <i class="bi bi-plus-lg me-2"></i> Add Operator
            </button>
        </div>

    </div>

    {{-- MODAL: Create/Edit Operator (Compact & Standard Labels) --}}
    <div class="modal fade" id="operatorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content rounded-3 border-0 shadow" method="POST" id="operatorForm" novalidate>
                @csrf
                <div class="modal-header border-bottom-0 pb-0 pt-3 px-3">
                    <h6 class="modal-title fw-bold text-uppercase" id="operatorModalLabel">Add Operator</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-2 px-3 pb-3">
                    <input type="hidden" name="operator_id" id="operator-id">

                    {{-- Row 1: ID & Name --}}
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label for="employeeID" class="form-label small fw-bold text-secondary mb-1">ID (5
                                Digits)</label>
                            <input type="text" class="form-control form-control-sm" id="employeeID" name="employeeID"
                                placeholder="Ex: 55211" minlength="5" maxlength="5" required inputmode="numeric">
                            <div class="invalid-feedback small">Must be 5 chars.</div>
                        </div>
                        <div class="col-8">
                            <label for="name" class="form-label small fw-bold text-secondary mb-1">Full Name</label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name"
                                placeholder="Enter name" required>
                        </div>
                    </div>

                    {{-- Row 2: Division & Leader --}}
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label for="division" class="form-label small fw-bold text-secondary mb-1">Division</label>
                            <select class="form-select form-select-sm" id="division" name="division_id" required>
                                <option value="" disabled selected>Select Division</option>
                                @foreach ($divisions as $departmentName => $deptDivisions)
                                    <optgroup label="{{ $departmentName }}">
                                        @foreach ($deptDivisions as $division)
                                            <option value="{{ $division->id }}"
                                                data-department="{{ $division->department_id }}">{{ $division->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="leaderModal" class="form-label small fw-bold text-secondary mb-1">Leader</label>
                            <select class="form-select form-select-sm" id="leaderModal" name="superior_id" required>
                                <option value="" disabled selected>Select Leader</option>
                                @foreach ($leaders as $leader)
                                    <option value="{{ $leader->employeeID }}"
                                        data-department="{{ $leader->department_id }}">{{ $leader->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-3 pb-3">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" id="btn-save">Save
                        Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Delete Confirmation (Small) --}}
    <div class="modal fade" id="deleteOperatorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form class="modal-content rounded-3 border-0 text-center p-3" id="deleteOperatorForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body p-1">
                    <div class="mb-2 text-danger"><i class="bi bi-trash3 fs-1"></i></div>
                    <h6 class="fw-bold">Delete Operator?</h6>
                    <p class="text-muted small mb-0">Sure delete <strong id="deleteOperatorName"
                            class="text-dark"></strong>?</p>
                </div>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3"
                        data-bs-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">Yes</button>
                </div>
            </form>
        </div>
    </div>

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            const ROUTES = {
                SEARCH: "{{ route('operator.search') }}",
                STORE: "{{ route('operator.store') }}",
                UPDATE: "{{ url('operator/update-operator') }}",
                DELETE: "{{ url('operator/delete-operator') }}"
            };

            let debounceTimer;
            let isSyncing = false; // Flag anti infinite-loop

            // --- 🚀 SMART FILTERING CACHE ---
            // Simpan HTML murni dari server
            const originalDivisionHTML = $('#division').html();
            const originalLeaderHTML = $('#leaderModal').html();

            // Bikin mapping Dept ID yang cepet diakses (O(1))
            const divisionDepts = {};
            $(originalDivisionHTML).filter('optgroup').find('option').each(function() {
                divisionDepts[$(this).val()] = $(this).data('department');
            });

            const leaderDepts = {};
            $(originalLeaderHTML).filter('option[data-department]').each(function() {
                leaderDepts[$(this).val()] = $(this).data('department');
            });

            // --- FUNGSI INIT SELECTIZE ---
            function initDivisionSelectize() {
                if ($('#division')[0].selectize) {
                    $('#division')[0].selectize.destroy();
                }
                $('#division').selectize({
                    sortField: 'text',
                    searchField: ['text'],
                    placeholder: 'Select Division...'
                });
            }

            // --- FUNGSI FILTER DIVISI ---
            function filterDivisions(deptId) {
                // 1. Selamatkan value yang lagi dipilih SEBELUM Selectize dihancurkan
                let currentVal = null;
                if ($('#division')[0].selectize) {
                    currentVal = $('#division')[0].selectize.getValue();
                } else {
                    currentVal = $('#division').val();
                }

                // 2. Bongkar dan balikin HTML ke default
                if ($('#division')[0].selectize) $('#division')[0].selectize.destroy();
                $('#division').html(originalDivisionHTML);

                // 3. Buang elemen yang gak sesuai departemen
                if (deptId) {
                    $('#division optgroup option').each(function() {
                        if ($(this).data('department') != deptId) {
                            $(this).remove();
                        }
                    });

                    // Bersihin optgroup yang isinya kosong
                    $('#division optgroup').each(function() {
                        if ($(this).children('option').length === 0) {
                            $(this).remove();
                        }
                    });
                }

                // 4. Bangun ulang Selectize
                initDivisionSelectize();

                // 5. Kembalikan value yang diselamatkan tadi JIKA masih ada di list yang baru
                if (currentVal) {
                    const selectizeInstance = $('#division')[0].selectize;
                    const optionExists = selectizeInstance.options[currentVal];

                    if (optionExists) {
                        // true = silent mode biar gak nge-trigger event 'change' lagi (mencegah loop)
                        selectizeInstance.setValue(currentVal, true);
                    }
                }
            }

            // --- FUNGSI FILTER LEADER ---
            function filterLeaders(deptId) {
                // 1. Selamatkan value Leader
                const currentVal = $('#leaderModal').val();

                // 2. Balikin HTML Leader ke default
                $('#leaderModal').html(originalLeaderHTML);

                // 3. Buang Leader yang beda departemen
                if (deptId) {
                    $('#leaderModal option[data-department]').each(function() {
                        if ($(this).data('department') != deptId) {
                            $(this).remove();
                        }
                    });
                }

                // 4. Kembalikan value Leader kalau masih valid
                if (currentVal && $('#leaderModal option[value="' + currentVal + '"]').length > 0) {
                    $('#leaderModal').val(currentVal);
                } else {
                    $('#leaderModal').val('');
                }
            }

            // --- INIT AWAL SAAT HALAMAN DILOUD ---
            initDivisionSelectize();
            fetchOperators();

            // --- CROSS-DEPENDENCY EVENTS ---

            // Pas Leader diganti -> Filter list Divisi
            $('#leaderModal').on('change', function() {
                if (isSyncing) return;
                isSyncing = true;

                const selectedLeaderId = $(this).val();
                const targetDept = selectedLeaderId ? leaderDepts[selectedLeaderId] : null;

                filterDivisions(targetDept);

                isSyncing = false;
            });

            // Pas Divisi diganti -> Filter list Leader
            $('#division').on('change', function() {
                if (isSyncing) return;
                isSyncing = true;

                const selectedDivId = $(this).val();
                const targetDept = selectedDivId ? divisionDepts[selectedDivId] : null;

                filterLeaders(targetDept);

                isSyncing = false;
            });

            // --- AJAX SEARCH LIST ---
            function fetchOperators(url = ROUTES.SEARCH) {
                $('#table-loader').removeClass('d-none');
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        keyword: $('#search-operator').val(),
                        leader: $('#leader').val()
                    },
                    success: function(response) {
                        $('#operator-table-body').html(response.html);
                        $('#pagination-links').html(response.pagination);
                        const selectedDept = $('#leader option:selected').data('department');
                        $('#department').val(selectedDept || '-');
                    },
                    complete: function() {
                        $('#table-loader').addClass('d-none');
                    }
                });
            }

            $('#search-operator').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchOperators(), 500);
            });

            $('#leader').on('change', function() {
                fetchOperators();
            });

            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                fetchOperators($(this).attr('href'));
            });

            // --- MODAL ACTIONS ---
            $('#btn-add-operator').click(function() {
                $('#operatorForm')[0].reset();
                $('#operatorForm').removeClass('was-validated');
                $('#operatorModalLabel').text('Add New Operator');
                $('#operatorForm').attr('action', ROUTES.STORE);
                $('#operatorForm').find('input[name="_method"]').remove();
                $('#employeeID').attr('readonly', false);

                // Reset Dropdown
                isSyncing = true;
                filterDivisions(null);
                filterLeaders(null);
                $('#division')[0].selectize.clear(true);
                isSyncing = false;
            });

            $(document).on('click', '.btn-edit-operator', function() {
                const btn = $(this);
                const id = btn.data('id');
                const leaderId = btn.data('leader');
                const divisionId = btn.data('division');

                $('#operator-id').val(id);
                $('#employeeID').val(btn.data('employeeid')).attr('readonly', true);
                $('#name').val(btn.data('name'));

                $('#operatorModalLabel').text('Edit Operator');
                $('#operatorForm').attr('action', `${ROUTES.UPDATE}/${id}`);

                if ($('#operatorForm').find('input[name="_method"]').length === 0) {
                    $('#operatorForm').prepend('<input type="hidden" name="_method" value="PUT">');
                }

                // 🚀 SET DATA DEPENDENT SECARA AMAN 🚀
                isSyncing = true; // Gembok biar event 'change' gak jalan liar

                // 1. Balikin ke list awal biar aman
                filterDivisions(null);
                filterLeaders(null);

                // 2. Set Leader-nya dulu (ini cuma nyari departemennya)
                $('#leaderModal').val(leaderId);
                const targetDept = leaderId ? leaderDepts[leaderId] : null;

                // 3. Filter list Divisi & Leader berdasarkan Departemen si Leader
                filterDivisions(targetDept);
                filterLeaders(targetDept);

                // 4. Set ulang value Leader karena barusan listnya dibongkar
                $('#leaderModal').val(leaderId);

                // 5. Set value Divisi di Selectize
                const divisionSelectize = $('#division')[0].selectize;
                if (divisionSelectize && divisionId) {
                    divisionSelectize.setValue(divisionId, true);
                }

                isSyncing = false; // Buka gembok

                bootstrap.Modal.getOrCreateInstance(document.getElementById('operatorModal')).show();
            });

            $(document).on('click', '.btn-delete-operator', function() {
                const id = $(this).data('id');
                $('#deleteOperatorForm').attr('action', `${ROUTES.DELETE}/${id}`);
                $('#deleteOperatorName').text($(this).data('name'));
            });

            $('#operatorForm').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    $('#btn-save').prop('disabled', true).text('Saving...');
                }
                $(this).addClass('was-validated');
            });

            $('#operatorModal').on('hidden.bs.modal', function() {
                $('#operatorForm')[0].reset();
                $('#operator-id').val('');
                $('#employeeID').attr('readonly', false);
                $('#btn-save').prop('disabled', false).text('Save Data');
                $('#operatorForm').removeClass('was-validated');

                // Kembalikan ke pilihan lengkap
                isSyncing = true;
                filterDivisions(null);
                filterLeaders(null);
                if ($('#division')[0].selectize) {
                    $('#division')[0].selectize.clear(true);
                }
                isSyncing = false;
            });
        });
    </script>
@endsection
