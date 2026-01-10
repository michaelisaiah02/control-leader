@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        MANAGEMENT USER
    </p>
@endpush

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-end justify-content-center align-items-center mb-3">
            <div class="col-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control" placeholder="Search..." id="search-user" autocomplete="off">
            </div>
        </div>
        <div class="table-responsive text-nowrap mb-3" style="max-height: 280px; overflow-y: auto;">
            <table class="table table-striped m-0" id="user-table">
                <thead class="table-primary sticky-top">
                    <tr class="text-center">
                        <th>No</th>
                        <th>ID User</th>
                        <th>User Name</th>
                        <th>Role</th>
                        <th>ID Superior</th>
                        <th>Superior Name</th>
                        <th>Departement</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="text-center row justify-content-between align-items-start">
            <div class="col-auto">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Back</a>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-lg text-white rounded-pill m-0 py-1" data-bs-toggle="modal"
                    data-bs-target="#userModal" id="btn-add-user">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit User -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" id="userForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="user-id">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="employeeID" class="form-label">ID User</label>
                            <input type="text" class="form-control" id="employeeID" name="employeeID" minlength="5"
                                maxlength="5" required>
                            <div class="invalid-feedback">ID User must be 5 characters.</div>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="" disabled selected>Choose Role</option>
                                <option value="management">Management</option>
                                <option value="ypq">YPQ Team</option>
                                <option value="admin">Admin</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="leader">Leader</option>
                                <option value="guest">Guest</option>
                            </select>
                            <div class="invalid-feedback">Role must be selected.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="name" class="form-label">User Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">Name is required.</div>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department_id">
                                <option value="" disabled selected>Choose Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Department must be selected.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6">
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>
                        <div class="col-6 mb-3" id="superiorForm">
                            <label for="superior" class="form-label">Superior</label>
                            <select class="form-select" id="superior" name="superior_id" required>
                                <option value="" disabled selected>Choose Superior</option>
                            </select>
                            <div class="invalid-feedback">Superior must be selected.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Delete User -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteUserForm" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the user named <strong id="deleteUserName"></strong>?</p>
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
        function fetchUsers(keyword = '', page = 1, role = '') {
            $('#loading').show();
            $.ajax({
                url: `{{ route('admin.users.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                    page: page,
                    role: role
                },
                success: function(response) {
                    $('#user-table-body').html(response.html);
                    $('#pagination-links').html(response.pagination);
                    $('html, body').animate({
                        scrollTop: $('#user-table').offset().top - 100
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

        function fetchSuperiors() {
            $.ajax({
                url: `{{ route('admin.users.getSuperiors') }}`,
                type: 'GET',
                data: {
                    role: $('#role').val(),
                    department_id: $('#department').val()
                },
                success: function(response) {
                    $('#superior').empty().append(
                        `<option value="" disabled selected>Choose Superior</option>`);
                    console.log(response[0]);
                    const superiors = response && Array.isArray(response[0].superiors) ? response[0].superiors :
                        [];
                    if (superiors.length === 0) {
                        $('#superior').append(`<option value="" disabled>No superior available</option>`);
                        return;
                    }
                    superiors.forEach(function(superior) {
                        $('#superior').append(
                            `<option value="${superior.id}">${superior.name} (${superior.employeeID})</option>`
                        );
                    });
                },
                error: function() {
                    alert('Gagal memuat data atasan.');
                }
            });
        }

        $(document).ready(function() {
            // Add User
            $('#btn-add-user').click(function() {
                $('#userForm').trigger('reset');
                $('#userModalLabel').text('Add User');
                $('#userForm').attr('action', "{{ route('admin.users.store') }}");
            });

            // Delegasi tombol Edit
            $(document).on('click', '.btn-edit-user', function() {
                const id = $(this).data('id');
                $('#user-id').val(id);
                $('#name').val($(this).data('name'));
                $('#employeeID').val($(this).data('employeeid'));
                $('#role').val($(this).data('role'));
                $('#password').val('');
                if ($(this).data('approved') === 1) {
                    $('#approved').attr('checked', true);
                } else {
                    $('#approved').attr('checked', false);
                }
                if ($(this).data('checked') === 1) {
                    $('#checked').attr('checked', true);
                } else {
                    $('#checked').attr('checked', false);
                }
                $('#userModalLabel').text('Edit User');
                $('#userForm').attr('action', `{{ url('admin/users/update-user') }}/${id}`);
                new bootstrap.Modal(document.getElementById('userModal')).show();
            });

            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete-user', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#deleteUserForm').attr('action', `{{ url('admin/users/delete-user') }}/${id}`);
                $('#deleteUserName').text(name);
            });

            const $superiorGroup = $('#superiorForm');
            $superiorGroup.hide();

            function toggleSuperior() {
                const roleFilled = $('#role').val();
                const departmentFilled = $('#department').val();

                if (roleFilled && departmentFilled) {
                    $superiorGroup.show();
                    fetchSuperiors();
                } else {
                    $superiorGroup.hide();
                    $('#superior').val('');
                }
            }

            $('#role, #department').on('change', toggleSuperior);

            // Form Validation
            $('.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });

            let debounceTimer;
            $('#search-user').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchUsers(keyword);
                }, 400);
            });

            // Initial fetch
            fetchUsers();
        });
    </script>
@endsection
