@extends('layouts.app')

@push('subtitle')
    {{-- Badge Header diperkecil dikit paddingnya biar imbang --}}
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-person-gear me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase">Management Division</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid dashboard-container pb-2">

        {{-- FILTER SECTION --}}
        <div class="card border-0 shadow-sm mb-2 rounded-3 shrink-0">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-auto">
                        <h6 class="fw-bold text-secondary mb-0 small text-uppercase">
                            <i class="bi bi-table me-1"></i>Division Data
                        </h6>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="search" class="form-control form-control-sm border-start-0 ps-2"
                                id="search-division" placeholder="Search Division..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE SECTION (Card Fill) --}}
        <div class="card-fill position-relative">

            {{-- Loading Overlay --}}
            <div id="table-loader"
                class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none z-3 d-flex justify-content-center align-items-center">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>

            <div class="table-responsive-wrapper">
                <table class="table table-sm table-hover table-striped mb-0 table-sticky-header text-nowrap"
                    id="division-table">
                    <thead class="text-secondary small text-uppercase fw-bold text-center table-primary">
                        <tr>
                            <th>No</th>
                            <th class="text-start">Name</th>
                            <th class="text-start">Department</th>
                            <th width="5%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="division-table-body" class="small align-middle">
                        {{-- Data injected by AJAX --}}
                    </tbody>
                </table>

            </div>
        </div>

        {{-- ACTION BAR --}}
        <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 me-2 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Back
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" id="btn-add-division"
                data-bs-toggle="modal" data-bs-target="#divisionModal">
                <i class="bi bi-plus-lg me-2"></i> Add Division
            </button>
        </div>
    </div>

    {{-- MODAL: Create/Edit Division (Versi Compact) --}}
    <div class="modal fade" id="divisionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form class="modal-content rounded-3 border-0 shadow" id="divisionForm" method="POST" novalidate>
                @csrf
                <div class="modal-header border-bottom-0 pb-0 pt-3 px-3">
                    <h6 class="modal-title fw-bold text-uppercase" id="divisionModalLabel">Add Division</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-2 px-3 pb-3">
                    <input type="hidden" name="division_id" id="division-id">

                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <label for="name" class="form-label small fw-bold text-secondary mb-1">Division Name</label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name"
                                placeholder="Name of division" required>
                            <div class="invalid-feedback">Please enter a name of division.</div>
                        </div>
                        <div class="col-12" id="departmentForm">
                            <label for="department" class="form-label small fw-bold text-secondary mb-1">Department</label>
                            <select class="form-select form-select-sm" id="department" name="department_id">
                                <option value="" disabled selected>Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a department.</div>
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

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            const ROUTES = {
                SEARCH: "{{ route('divisions.search') }}",
                STORE: "{{ route('divisions.store') }}",
                UPDATE: "{{ url('divisions/update-division') }}"
            };

            let debounceTimer;

            // 1. Fetch Divisions Logic
            function fetchDivisions(keyword = '', page = 1) {
                $('#table-loader').removeClass('d-none');

                $.ajax({
                    url: ROUTES.SEARCH,
                    type: 'GET',
                    data: {
                        keyword: keyword,
                        page: page
                    },
                    success: function(response) {
                        $('#division-table-body').html(response.html);
                        $('#pagination-links').html(response.pagination);

                        // Optional: Sync column widths if needed
                    },
                    error: function() {
                        alert('Failed to load data.');
                    },
                    complete: function() {
                        $('#table-loader').addClass('d-none');
                    }
                });
            }

            // Event Listeners
            $('#search-division').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => fetchDivisions(keyword), 400);
            });

            // Modal Actions
            $('#btn-add-division').click(function() {
                $('#divisionForm')[0].reset();
                $('#divisionForm').removeClass('was-validated');
                $('#divisionModalLabel').text('Add New Division');
                $('#divisionForm').attr('action', ROUTES.STORE);
                $('#divisionForm').find('input[name="_method"]').remove();
                setTimeout(() => {
                    $('#name').focus();
                }, 500);
            });

            $(document).on('click', '.btn-edit-division', function() {
                const btn = $(this);
                const id = btn.data('id');
                $('#division-id').val(id);
                $('#name').val(btn.data('name'));
                $('#department').val(btn.data(
                    'department')); // Pastikan data attribute ada di button edit
                $('#divisionModalLabel').text('Edit Division');
                $('#divisionForm').attr('action', `${ROUTES.UPDATE}/${id}`);

                if ($('#divisionForm').find('input[name="_method"]').length === 0) {
                    $('#divisionForm').prepend('<input type="hidden" name="_method" value="POST">');
                }

                const myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById(
                    'divisionModal'));
                myModal.show();
            });

            $('#divisionForm').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    $('#btn-save').prop('disabled', true).text('Saving...');
                }
                $(this).addClass('was-validated');
            });

            // Initial Load
            fetchDivisions();
        });
    </script>
@endsection
