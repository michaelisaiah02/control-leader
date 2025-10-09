@extends('layouts.app')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-between justify-content-center align-items-center mb-3">
            <div class="col-auto">
                <h3 class="mb-0">Acc Criteria Management</h3>
            </div>
            <div class="col-auto ms-md-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control" placeholder="Search..." id="search-standard" autocomplete="off">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standardModal"
                    id="btn-add-standard">
                    Add New Acc Criteria
                </button>
            </div>
        </div>

        <div class="table-responsive text-nowrap mb-3">
            <table class="table table-striped m-0" id="standard-table">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>ID Number</th>
                        <th>Equipment Name</th>
                        <th>Capacity</th>
                        <th>Accuracy</th>
                        <th>Parameter 1</th>
                        <th>Parameter 2</th>
                        <th>Parameter 3</th>
                        <th>Parameter 4</th>
                        <th>Parameter 5</th>
                        <th>Parameter 6</th>
                        <th>Parameter 7</th>
                        <th>Parameter 8</th>
                        <th>Parameter 9</th>
                        <th>Parameter 10</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="standard-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="text-center row justify-content-between align-items-start">
            <div id="pagination-links" class="col-md col align-items-center"
                data-url="{{ route('kalibrasi.admin.standards.search') }}">
                {{-- Generate by AJAX --}}
            </div>
            <div class="col-auto">
                <a href="{{ route('dashboard', ['key' => 'master-data', 'application' => 'KALIBRASI']) }}"
                    class="btn btn-primary">Close</a>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Standard -->
    <div class="modal fade" id="standardModal" tabindex="-1" aria-labelledby="standardModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" id="standardForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="standardModalLabel">Add Standard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-floating">
                        <select class="form-select" id="id-num" name="id_num" required>
                            @foreach ($masterLists as $masterList)
                                <option value="{{ $masterList->id_num }}">{{ $masterList->id_num }} -
                                    {{ $masterList->equipment->name }}</option>
                            @endforeach
                        </select>
                        <label for="id-num" class="form-label">ID Number</label>
                    </div>
                    <div class="mb-3">
                        <div class="invalid-feedback">ID Number is required.</div>
                    </div>
                    <div class="input-group input-group-sm mb-3">
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control form-control-sm" id="param-01"
                                placeholder="Parameter 1" name="param_01" required>
                            <label for="param-01">Parameter 1</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control form-control-sm" id="param-02"
                                placeholder="Parameter 2" name="param_02" required>
                            <label for="param-02">Parameter 2</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-03"
                                placeholder="Parameter 3" name="param_03" required>
                            <label for="param-03">Parameter 3</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-04"
                                placeholder="Parameter 4" name="param_04" required>
                            <label for="param-04">Parameter 4</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-05"
                                placeholder="Parameter 5" name="param_05" required>
                            <label for="param-05">Parameter 5</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-06"
                                placeholder="Parameter 6" name="param_06" required>
                            <label for="param-06">Parameter 6</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-07"
                                placeholder="Parameter 7" name="param_07" required>
                            <label for="param-07">Parameter 7</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-08"
                                placeholder="Parameter 8" name="param_08" required>
                            <label for="param-08">Parameter 8</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-09"
                                placeholder="Parameter 9" name="param_09" required>
                            <label for="param-09">Parameter 9</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" step="0.00001" class="form-control" id="param-10"
                                placeholder="Parameter 10" name="param_10" required>
                            <label for="param-10">Parameter 10</label>
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

    <!-- Modal Delete Standard -->
    <div class="modal fade" id="deleteStandardModal" tabindex="-1" aria-labelledby="deleteStandardModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteStandardForm" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStandardModalLabel">Delete Standard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the acceptance criteria for <strong
                            id="deleteEquipmentName"></strong>?</p>
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
        function fetchStandards(keyword = '', page = 1) {
            $('#loading').show();
            $.ajax({
                url: `{{ route('kalibrasi.admin.standards.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                    page: page
                },
                success: function(response) {
                    $('#standard-table-body').html(response.html);
                    $('#pagination-links').html(response.pagination);
                    $('html, body').animate({
                        scrollTop: $('#standard-table').offset().top - 100
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
            // Add Standard
            $('#btn-add-standard').click(function() {
                $('#standardForm').trigger('reset');
                $('#standardModalLabel').text('Add Standard');
                $('#standardForm').attr('action', "{{ route('kalibrasi.admin.standards.store') }}");
                $('#password-group').show();
            });

            // Delegasi tombol Edit
            $(document).on('click', '.btn-edit-standard', function() {
                const id = $(this).data('id');
                const idNum = $(this).data('id-num');
                $('#standard-id').val(id);
                $('#id-num').val(idNum).change();
                $('#param-01').val($(this).data('param01'));
                $('#param-02').val($(this).data('param02'));
                $('#param-03').val($(this).data('param03'));
                $('#param-04').val($(this).data('param04'));
                $('#param-05').val($(this).data('param05'));
                $('#param-06').val($(this).data('param06'));
                $('#param-07').val($(this).data('param07'));
                $('#param-08').val($(this).data('param08'));
                $('#param-09').val($(this).data('param09'));
                $('#param-10').val($(this).data('param10'));
                $('#standardForm').attr('action',
                    `{{ url('kalibrasi/admin/standards/update-standard') }}/${id}`);
                new bootstrap.Modal(document.getElementById('standardModal')).show();
            });

            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete-standard', function() {
                const id = $(this).data('id');
                const idNum = $(this).data('id-num');
                const equipment = $(this).data('equipment');
                $('#deleteStandardForm').attr('action',
                    `{{ url('kalibrasi/admin/standards/delete-standard') }}/${id}`);
                $('#deleteEquipmentName').text(`${idNum} - ${equipment}`);
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
            $('#search-standard').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchStandards(keyword);
                }, 400);
            });

            // AJAX pagination
            $(document).on('click', '#pagination-links .pagination a', function(e) {
                e.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                const keyword = $('#search-standard').val();
                fetchStandards(keyword, page);
            });

            // Initial fetch
            fetchStandards();
        });
    </script>
    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const tag = e.target.tagName.toLowerCase();
                if (['input', 'textarea', 'select'].includes(tag)) {
                    e.preventDefault(); // Hindari submit default

                    const form = e.target.closest('form');
                    const elements = Array.from(form.querySelectorAll('input, textarea, select'))
                        .filter(el => el.type !== 'hidden' && !el.disabled);

                    // Cek apakah ada yang kosong
                    const emptyElement = elements.find(el => !el.value.trim());

                    if (emptyElement) {
                        emptyElement.focus();
                    } else {
                        form.submit(); // Semua terisi, submit form
                    }
                }
            }
        });
    </script>
@endsection
