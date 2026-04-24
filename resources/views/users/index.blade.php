@extends('layouts.app')

@push('subtitle')
    {{-- Badge Header diperkecil dikit paddingnya biar imbang --}}
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-person-gear me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase">Management User</span>
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
                            <i class="bi bi-table me-1"></i>User Data
                        </h6>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="search" class="form-control form-control-sm border-start-0 ps-2" id="search-user"
                                placeholder="Search User..." autocomplete="off">
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
                    id="user-table">
                    <thead class="text-secondary small text-uppercase fw-bold text-center table-primary">
                        <tr>
                            <th>No</th>
                            <th>ID</th>
                            <th class="text-start">Name</th>
                            <th>Role</th>
                            <th>Superior ID</th>
                            <th>Superior Name</th>
                            <th>Dept</th>
                            <th width="5%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body" class="small align-middle">
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
            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" id="btn-add-user"
                data-bs-toggle="modal" data-bs-target="#userModal">
                <i class="bi bi-plus-lg me-2"></i> Add User
            </button>
        </div>
    </div>

    {{-- MODAL: Create/Edit User (Versi Compact) --}}
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content rounded-3 border-0 shadow" id="userForm" method="POST" novalidate>
                @csrf
                <div class="modal-header border-bottom-0 pb-0 pt-3 px-3">
                    <h6 class="modal-title fw-bold text-uppercase" id="userModalLabel">Add User</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-2 px-3 pb-3">
                    <input type="hidden" name="user_id" id="user-id">

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-6">
                            <label for="employeeID" class="form-label small fw-bold text-secondary mb-1">User ID (5
                                Digits)</label>
                            <input type="text" class="form-control form-control-sm" id="employeeID" name="employeeID"
                                placeholder="Ex: 12345" minlength="5" maxlength="5" required inputmode="numeric">
                            <div class="invalid-feedback small">Must be exactly 5 characters.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="role" class="form-label small fw-bold text-secondary mb-1">Role</label>
                            <select class="form-select form-select-sm" id="role" name="role" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="management">Management</option>
                                <option value="ypq">YPQ Team</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="leader">Leader</option>
                                <option value="guest">Guest</option>
                            </select>
                            <div class="invalid-feedback">Please select a role.</div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label small fw-bold text-secondary mb-1">Full Name</label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name"
                                placeholder="John Doe" required>
                            <div class="invalid-feedback">Please enter a full name.</div>
                        </div>
                        <div class="col-12 col-md-6" id="departmentForm">
                            <label for="department" class="form-label small fw-bold text-secondary mb-1">Department</label>
                            <select class="form-select form-select-sm" id="department" name="department_id">
                                <option value="" disabled selected>Select Dept</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a department.</div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label small fw-bold text-secondary mb-1">Password</label>
                            <div class="input-group input-group-sm has-validation">
                                <input type="password" class="form-control" id="password" name="password"
                                    minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$"
                                    autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Min 8 chars, Uppercase, Lowercase, Number, Symbol.
                                </div>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Leave empty if unchanged (Edit
                                Mode)</small>
                        </div>
                        <div class="col-12 col-md-6" id="superiorForm" style="display: none;">
                            <label for="superior" class="form-label small fw-bold text-secondary mb-1">Superior</label>
                            <select class="form-select form-select-sm" id="superior" name="superior_id">
                                <option value="" disabled selected>Select Superior</option>
                            </select>
                            <div class="invalid-feedback">Please select a superior.</div>
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

    {{-- MODAL: Delete User (Small) --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form class="modal-content rounded-3 border-0 text-center p-3" id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body p-1">
                    <div class="mb-2 text-danger"><i class="bi bi-trash3 fs-1"></i></div>
                    <h6 class="fw-bold">Delete User?</h6>
                    <p class="text-muted small mb-0">Sure delete <strong id="deleteUserName" class="text-dark"></strong>?
                    </p>
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
                SEARCH: "{{ route('users.search') }}",
                STORE: "{{ route('users.store') }}",
                UPDATE: "{{ url('users/update-user') }}",
                DELETE: "{{ url('users/delete-user') }}",
                SUPERIORS: "{{ route('users.getSuperiors') }}"
            };

            let debounceTimer;

            // 1. Fetch Users Logic
            function fetchUsers(keyword = '', page = 1) {
                $('#table-loader').removeClass('d-none');

                $.ajax({
                    url: ROUTES.SEARCH,
                    type: 'GET',
                    data: {
                        keyword: keyword,
                        page: page
                    },
                    success: function(response) {
                        $('#user-table-body').html(response.html);
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

            // 2. Fetch Superiors Logic (Dependency Dropdown)
            function fetchSuperiors(selectedValue = null) {
                const role = $('#role').val();
                const deptId = $('#department').val();
                const $supSelect = $('#superior');

                // if (!role || !deptId) return;

                // Loading state di dropdown
                $supSelect.html('<option>Loading...</option>');

                $.ajax({
                    url: ROUTES.SUPERIORS,
                    type: 'GET',
                    data: {
                        role: role,
                        department_id: deptId
                    },
                    success: function(response) {
                        $supSelect.empty().append(
                            '<option value="" disabled selected>Select Superior</option>');

                        // --- PERBAIKAN DI SINI ---
                        // Cek apakah response langsung array atau object dengan key superiors
                        let superiors = [];
                        if (Array.isArray(response)) {
                            superiors = response; // Jika response langsung [ {id:..}, {id:..} ]
                        } else if (response.superiors) {
                            superiors = response.superiors; // Jika response { superiors: [...] }
                        }

                        if (superiors.length === 0) {
                            $supSelect.append(
                                '<option value="" disabled>No superior available</option>');
                        } else {
                            superiors.forEach(sup => {
                                $supSelect.append(
                                    `<option value="${sup.employeeID}">${sup.name} (${sup.employeeID})</option>`
                                );
                            });
                        }

                        // Set Selected Value jika ada (Mode Edit)
                        if (selectedValue) {
                            $supSelect.val(selectedValue);
                        }
                    },
                    error: function() {
                        $supSelect.html('<option value="" disabled>Error loading</option>');
                    }
                });
            }

            function toggleDepartmentVisibility() {
                if ($('#role').val() === 'leader' || $('#role').val() === 'supervisor') {
                    $('#departmentForm').fadeIn();
                    $('#departmentForm').find('select').attr('required', true);
                } else {
                    $('#departmentForm').hide();
                    $('#departmentForm').find('select').attr('required', false);
                    $('#department').val('');
                }
            }

            // Toggle Superior Field Visibility
            function toggleSuperiorVisibility() {
                if ($('#role').val() !== 'management' && $('#role').val() !== 'guest') {
                    if ($('#role').val() === 'ypq' || $('#department').val()) {
                        $('#superiorForm').fadeIn();
                        $('#superiorForm').find('select').attr('required', true);
                        fetchSuperiors();
                    } else {
                        $('#superiorForm').hide();
                        $('#superiorForm').find('select').attr('required', false);
                        $('#superior').val('');
                    }
                } else {
                    $('#superiorForm').hide();
                    $('#superiorForm').find('select').attr('required', false);
                    $('#superior').val('');
                }
            }

            // Event Listeners
            $('#search-user').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => fetchUsers(keyword), 400);
            });

            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = new URL($(this).attr('href'));
                fetchUsers($('#search-user').val(), url.searchParams.get('page'));
            });

            // Dependency Change Events
            $('#role').on('change', toggleDepartmentVisibility);
            $('#role, #department').on('change', toggleSuperiorVisibility);

            // Modal Actions
            $('#btn-add-user').click(function() {
                $('#userForm')[0].reset();
                $('#userForm').removeClass('was-validated');
                $('#userModalLabel').text('Add New User');
                $('#userForm').attr('action', ROUTES.STORE);
                $('#userForm').find('input[name="_method"]').remove();
                $('#superiorForm').hide(); // Reset hide

                // Password wajib kalau add new
                $('#password').attr('required', true);
            });

            $(document).on('click', '.btn-edit-user', function() {
                const btn = $(this);
                const id = btn.data('id');
                $('#user-id').val(id);
                $('#name').val(btn.data('name'));
                $('#employeeID').val(btn.data('employeeid'));
                $('#role').val(btn.data('role'));
                $('#department').val(btn.data(
                    'department')); // Pastikan data attribute ada di button edit
                $('#password').val('');
                $('#password').removeAttr('required'); // Password optional kalau edit
                $('#superior').val(btn.data('superior'));

                $('#userModalLabel').text('Edit User');
                $('#userForm').attr('action', `${ROUTES.UPDATE}/${id}`);

                if ($('#userForm').find('input[name="_method"]').length === 0) {
                    $('#userForm').prepend('<input type="hidden" name="_method" value="POST">');
                }

                // Trigger logic superior & department visibility
                toggleSuperiorVisibility();
                toggleDepartmentVisibility();

                fetchSuperiors(btn.data('superior'));

                const myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal'));
                myModal.show();
            });

            $(document).on('click', '.btn-delete-user', function() {
                $('#deleteUserForm').attr('action', `${ROUTES.DELETE}/${$(this).data('id')}`);
                $('#deleteUserName').text($(this).data('name'));
            });

            $('#userForm').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    $('#btn-save').prop('disabled', true).text('Saving...');
                }
                $(this).addClass('was-validated');
            });

            // Toggle Password
            $('#togglePassword').on('click', function() {
                const $input = $('#password');
                const $icon = $(this).find('i');
                const type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
                $icon.toggleClass('bi-eye bi-eye-slash');
            });

            // Initial Load
            fetchUsers();
        });
    </script>
@endsection
