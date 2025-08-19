@extends('layouts.app')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-between justify-content-center align-items-center mb-3">
            <div class="col-auto">
                <h3 class="mb-0">Equipments Management</h3>
            </div>
            <div class="col-auto ms-md-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control" placeholder="Search..." id="search-equipment" autocomplete="off">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#equipmentModal"
                    id="btn-add-equipment">
                    Add New Equipment
                </button>
            </div>
        </div>

        <div class="table-responsive text-nowrap mb-3">
            <table class="table table-striped m-0" id="equipment-table">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>Type ID</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="equipment-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="text-center row justify-content-between align-items-start">
            <div id="pagination-links" class="col-md col align-items-center"
                data-url="{{ route('kalibrasi.admin.equipments.search') }}">
                {{-- Generate by AJAX --}}
            </div>
            <div class="col-auto">
                <a href="{{ route('dashboard', ['key' => 'master-data', 'application' => 'KALIBRASI']) }}"
                    class="btn btn-primary">Close</a>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Equipment -->
    <div class="modal fade" id="equipmentModal" tabindex="-1" aria-labelledby="equipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" id="equipmentForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="equipmentModalLabel">Add Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="equipment_id" id="equipment-id">
                    <div class="mb-3">
                        <label for="type-id" class="form-label">Type ID</label>
                        <input type="text" class="form-control" id="type-id" name="type_id" minlength="3"
                            maxlength="3" autocomplete="off" required>
                        <div class="invalid-feedback">Type ID is required and must be exactly 3 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" autocomplete="off"
                            required>
                        <div class="invalid-feedback">Name is required.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete Equipment -->
    <div class="modal fade" id="deleteEquipmentModal" tabindex="-1" aria-labelledby="deleteEquipmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteEquipmentForm" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEquipmentModalLabel">Delete Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the equipment named <strong id="deleteEquipmentName"></strong>?</p>
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
        function fetchEquipments(keyword = '', page = 1) {
            $('#loading').show();
            $.ajax({
                url: `{{ route('kalibrasi.admin.equipments.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                    page: page
                },
                success: function(response) {
                    $('#equipment-table-body').html(response.html);
                    $('#pagination-links').html(response.pagination);
                    $('html, body').animate({
                        scrollTop: $('#equipment-table').offset().top - 100
                    }, 300);
                    $('.pagination nav').addClass('w-100');
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
            // Add Equipment
            $('#btn-add-equipment').click(function() {
                $('#equipmentForm').trigger('reset');
                $('#equipmentModalLabel').text('Add Equipment');
                $('#equipmentForm').attr('action', "{{ route('kalibrasi.admin.equipments.store') }}");
                $('#password-group').show();
            });

            // Delegasi tombol Edit
            $(document).on('click', '.btn-edit-equipment', function() {
                const id = $(this).data('id');
                $('#equipment-id').val(id);
                $('#name').val($(this).data('name'));
                $('#type-id').val($(this).data('type-id'));
                $('#equipmentForm').attr('action',
                    `{{ url('kalibrasi/admin/equipments/update-equipment') }}/${id}`);
                new bootstrap.Modal(document.getElementById('equipmentModal')).show();
            });

            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete-equipment', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#deleteEquipmentForm').attr('action',
                    `{{ url('kalibrasi/admin/equipments/delete-equipment') }}/${id}`);
                $('#deleteEquipmentName').text(name);
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
            $('#search-equipment').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchEquipments(keyword);
                }, 400);
            });

            // AJAX pagination
            $(document).on('click', '#pagination-links .pagination a', function(e) {
                e.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                const keyword = $('#search-equipment').val();
                fetchEquipments(keyword, page);
            });

            // Initial fetch
            fetchEquipments();
        });
    </script>
@endsection
