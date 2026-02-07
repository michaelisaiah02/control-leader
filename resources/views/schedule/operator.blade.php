@extends('layouts.app')

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-3 py-2 mt-1 mb-0 rounded-3 bg-white bg-opacity-10 border border-light text-white subtitle">
        <i class="bi bi-people-fill me-2"></i>
        <span class="fw-bold text-uppercase">Operator Management</span>
    </div>
@endpush

@section('content')
    {{-- Layout Fixed: Halaman Gak Bisa Scroll, Cuma Tabel yang Scroll --}}
    <div class="container-fluid layout-fixed pb-2">

        {{-- SECTION 1: FILTER (Compact) --}}
        <div class="card border-0 shadow-sm mb-2 rounded-3 flex-shrink-0">
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
                                        data-department="{{ $leader->department->department_name ?? '-' }}">
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
                    <thead class="text-secondary small text-uppercase fw-bold">
                        <tr>
                            {{-- Lebar kolom otomatis sinkron karena satu tabel --}}
                            <th>#</th>
                            <th>ID</th>
                            <th>Name</th>
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
            <div class="card-footer bg-white border-top p-1 flex-shrink-0">
                <div id="pagination-links" class="d-flex justify-content-center justify-content-md-end small"></div>
            </div>
        </div>

        {{-- SECTION 3: ACTION BAR --}}
        <div class="action-bar-static d-flex justify-content-between align-items-center px-2 mt-2">
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
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="leaderModal" class="form-label small fw-bold text-secondary mb-1">Direct
                                Supervisor</label>
                            <select class="form-select form-select-sm" id="leaderModal" name="superior_id" required>
                                <option value="" disabled selected>Select Leader</option>
                                @foreach ($leaders as $leader)
                                    <option value="{{ $leader->employeeID }}">{{ $leader->name }}</option>
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

            function fetchOperators(url = ROUTES.SEARCH) {
                $('#table-loader').removeClass('d-none');
                const keyword = $('#search-operator').val();
                const leader = $('#leader').val();

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        keyword: keyword,
                        leader: leader
                    },
                    success: function(response) {
                        $('#operator-table-body').html(response.html);
                        $('#pagination-links').html(response.pagination);

                        // Update Department field
                        const selectedDept = $('#leader option:selected').data('department');
                        $('#department').val(selectedDept || '-');

                        // Logic Penting: Sinkronisasi lebar kolom Header & Body
                        // Karena headernya terpisah, kita harus pastiin lebarnya sama
                        syncColumnWidths();
                    },
                    error: function() {
                        alert('Error loading data.');
                    },
                    complete: function() {
                        $('#table-loader').addClass('d-none');
                    }
                });
            }

            // Helper untuk menyamakan lebar kolom header dan body (Karena table headernya dipisah)
            function syncColumnWidths() {
                // Kita set width manual di HTML (width="5%") jadi harusnya aman.
                // Tapi kalau mau perfect, script ini bisa dipake nanti.
            }

            // --- Event Listeners ---
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

            // --- Modal Actions ---
            $('#btn-add-operator').click(function() {
                $('#operatorForm')[0].reset();
                $('#operatorForm').removeClass('was-validated');
                $('#operatorModalLabel').text('Add New Operator');
                $('#operatorForm').attr('action', ROUTES.STORE);
                $('#operatorForm').find('input[name="_method"]').remove();
            });

            $(document).on('click', '.btn-edit-operator', function() {
                const btn = $(this);
                const id = btn.data('id');
                $('#operator-id').val(id);
                $('#employeeID').val(btn.data('employeeid'));
                $('#name').val(btn.data('name'));
                $('#division').val(btn.data('division'));
                $('#leaderModal').val(btn.data('leader'));
                $('#operatorModalLabel').text('Edit Operator');
                $('#operatorForm').attr('action', `${ROUTES.UPDATE}/${id}`);
                if ($('#operatorForm').find('input[name="_method"]').length === 0) {
                    $('#operatorForm').prepend('<input type="hidden" name="_method" value="PUT">');
                }
                $('#operatorModal').modal('show');
            });

            $(document).on('click', '.btn-delete-operator', function() {
                const id = $(this).data('id');
                $('#deleteOperatorForm').attr('action', `${ROUTES.DELETE}/${id}`);
                $('#deleteOperatorName').text($(this).data('name'));
                $('#deleteOperatorModal').modal('show');
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

            // Initial Load
            fetchOperators();
        });
    </script>
@endsection
