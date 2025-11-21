@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        {{ $title }}
    </p>
@endpush

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-between justify-content-center align-items-center mb-3">
            <div class="col-auto my-2 my-md-0 d-flex align-items-center gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-secondary-subtle fw-bold" id="basic-addon1">Leader</span>
                    <select name="leader" id="leader" class="form-select bg-warning-subtle" onchange="">
                        <option value="" disabled selected>Pilih Leader</option>
                        @foreach ($leaders as $leader)
                            <option value="{{ $leader->id }}"
                                data-department="{{ $leader->department->department_name }}">{{ $leader->name }}
                                ({{ $leader->employeeID }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-secondary-subtle fw-bold" id="basic-addon1">Dept.</span>
                    <input type="text" class="form-control bg-warning-subtle text-center" placeholder="-"
                        aria-label="Department" aria-describedby="basic-addon1" id="department" disabled>
                </div>
            </div>
            <div class="col-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control bg-secondary-subtle" placeholder="Search..." id="search-operator"
                    autocomplete="off">
            </div>
        </div>

        <div class="table-responsive text-nowrap mb-3">
            <table class="table table-striped m-0 table-sm align-middle" id="operator-table">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>No</th>
                        <th>ID Operator</th>
                        <th class="text-start">Nama Operator</th>
                        <th>Bagian</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="operator-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="text-center row justify-content-between align-items-start">
            <div class="col-auto">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Back</a>
            </div>
            <div id="pagination-links" class="col-md col align-items-center"
                data-url="{{ route('control.operator.search') }}">
                {{-- Generate by AJAX --}}
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-lg text-white rounded-circle" data-bs-toggle="modal"
                    data-bs-target="#operatorModal" id="btn-add-operator">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Operator -->
    <div class="modal fade" id="operatorModal" tabindex="-1" aria-labelledby="operatorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" id="operatorForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="operatorModalLabel">Input Data Operator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="operator_id" id="operator-id">
                    <div class="mb-3">
                        <label for="employeeID" class="form-label">ID Operator</label>
                        <input type="text" class="form-control" id="employeeID" name="employeeID" minlength="5"
                            maxlength="5" required>
                        <div class="invalid-feedback">ID Operator minimal 5 karakter.</div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Operator</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Nama operator wajib.</div>
                    </div>
                    <div class="mb-3">
                        <label for="division" class="form-label">Bagian</label>
                        <select class="form-select" id="division" name="division_id" required>
                            <option value="" disabled selected>Pilih Bagian</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Bagian harus dipilih</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Delete Operator -->
    <div class="modal fade" id="deleteOperatorModal" tabindex="-1" aria-labelledby="deleteOperatorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteOperatorForm" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteOperatorModalLabel">Delete Operator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the operator named <strong id="deleteOperatorName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        function fetchOperators(keyword = '', page = 1, leader = '') {
            const DEPARTMENT = $('#leader option:selected').data('department') || '-';
            $('#loading').show();
            $.ajax({
                url: `{{ route('control.operator.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                    page: page,
                    leader: leader
                },
                success: function(response) {
                    $('#operator-table-body').html(response.html);
                    $('#pagination-links').html(response.pagination);
                    $('html, body').animate({
                        scrollTop: $('#operator-table').offset().top - 100
                    }, 300);
                    $('.pagination nav').addClass('w-100');
                    $('#department').val(DEPARTMENT);
                },
                complete: function() {
                    $('#loading').hide();
                },
                error: function() {
                    alert('Gagal memuat data.');
                }
            });
        }

        $(document).ready(function() {
            // Add Operator
            $('#btn-add-operator').click(function() {
                $('#operatorForm').trigger('reset');
                $('#operatorModalLabel').text('INPUT DATA OPERATOR');
                $('#operatorForm').attr('action', "{{ route('control.operator.store') }}");
            });

            // Delegasi tombol Edit
            $(document).on('click', '.btn-edit-operator', function() {
                const id = $(this).data('id');
                $('#operator-id').val(id);
                $('#name').val($(this).data('name'));
                $('#employeeID').val($(this).data('employeeid'));
                $('#division').val($(this).data('division'));
                $('#operatorModalLabel').text('EDIT DATA OPERATOR');
                $('#operatorForm').attr('action', `{{ url('control/operator/update-operator') }}/${id}`);
                new bootstrap.Modal(document.getElementById('operatorModal')).show();
            });

            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete-operator', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#deleteOperatorForm').attr('action',
                    `{{ url('control/operator/delete-operator') }}/${id}`);
                $('#deleteOperatorName').text(name);
            });

            // Form Validation
            $('.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });

            let debounceTimer;
            $('#search-operator').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchOperators(keyword);
                }, 400);
            });

            // AJAX pagination
            $(document).on('click', '#pagination-links .pagination a', function(e) {
                e.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                const keyword = $('#search-operator').val();
                fetchOperators(keyword, page);
            });


            $('#filter-role').on('change', function() {
                const keyword = $('#search-operator').val();
                const role = $(this).val();
                fetchOperators(keyword, 1, role); // reset ke halaman 1
            });

            // Initial fetch
            fetchOperators();
        });
    </script>
@endsection
