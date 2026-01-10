@extends('layouts.app')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-between justify-content-center align-items-center mb-3">
            <div class="col-auto">
                <h3 class="mb-0">Users Management</h3>
            </div>
            <div class="col-auto ms-md-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control" placeholder="Search..." id="search-user" autocomplete="off">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="btn-add-user">
                    Add New User
                </button>
            </div>
        </div>

        <div class="table-responsive text-nowrap mb-3">
            <table class="table table-striped m-0" id="user-table">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Approved</th>
                        <th>Checked</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="text-center row justify-content-between align-items-start">
            <div id="pagination-links" class="col-md col align-items-center"
                data-url="{{ route('kalibrasi.admin.users.search') }}">
                {{-- Generate by AJAX --}}
            </div>
            <div class="col-auto">
                <a href="{{ route('dashboard', ['key' => 'master-data', 'application' => 'KALIBRASI']) }}"
                    class="btn btn-primary">Close</a>
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
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Name is required.</div>
                    </div>
                    <div class="mb-3">
                        <label for="employeeID" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employeeID" name="employeeID" minlength="5"
                            maxlength="5" required>
                        <div class="invalid-feedback">Employee ID must be 5 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="" disabled selected>Choose Role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="guest">Guest</option>
                        </select>
                        <div class="invalid-feedback">Role must be selected.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8"
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$" required>
                        <div class="invalid-feedback">
                            Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.
                        </div>
                    </div>
                    <div class="col d-flex align-items-center justify-content-around">
                        <div class="col d-flex align-items-center">
                            <input type="hidden" name="approved" value="0">
                            <input class="form-check-input mt-0" type="checkbox" value="1" id="approved"
                                name="approved">
                            <label for="approved" class="form-label p-0 m-0 ms-2">Approved</label>
                        </div>
                        <div class="col d-flex align-items-center">
                            <input type="hidden" name="checked" value="0">
                            <input class="form-check-input mt-0" type="checkbox" value="1" id="checked"
                                name="checked">
                            <label for="checked" class="form-label p-0 m-0 ms-2">Checked</label>
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
        function fetchUsers(keyword = '', page = 1) {
            $('#loading').show();
            $.ajax({
                url: `{{ route('kalibrasi.admin.users.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                    page: page
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

        $(document).ready(function() {
            // Add User
            $('#btn-add-user').click(function() {
                $('#userForm').trigger('reset');
                $('#userModalLabel').text('Add User');
                $('#userForm').attr('action', "{{ route('kalibrasi.admin.users.store') }}");
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
                $('#userForm').attr('action', `{{ url('kalibrasi/admin/users/update-user') }}/${id}`);
                new bootstrap.Modal(document.getElementById('userModal')).show();
            });

            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete-user', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#deleteUserForm').attr('action', `{{ url('kalibrasi/admin/users/delete-user') }}/${id}`);
                $('#deleteUserName').text(name);
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
            $('#search-user').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchUsers(keyword);
                }, 400);
            });

            // AJAX pagination
            $(document).on('click', '#pagination-links .pagination a', function(e) {
                e.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                const keyword = $('#search-user').val();
                fetchUsers(keyword, page);
            });

            // Initial fetch
            fetchUsers();
        });
    </script>
@endsection
