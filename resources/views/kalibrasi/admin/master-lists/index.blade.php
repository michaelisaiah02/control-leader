@extends('layouts.app')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-between justify-content-center align-items-center mb-3">
            <div class="col-auto">
                <h3 class="mb-0">Master Lists Management</h3>
            </div>
            <div class="col-auto ms-md-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control" placeholder="Search..." id="search-master-list"
                    autocomplete="off">
            </div>
        </div>

        <div class="table-responsive text-nowrap mb-3">
            <table class="table table-striped m-0" id="master-list-table">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>ID Num</th>
                        <th>SN Num</th>
                        <th>Capacity</th>
                        <th>Accuracy</th>
                        <th>Brand</th>
                        <th>Calibration Type</th>
                        <th>1st Used</th>
                        <th>Rank</th>
                        <th>Calibration Freq</th>
                        <th>Acc Criteria</th>
                        <th>PIC</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="master-list-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="text-center row justify-content-between align-items-center">
            <div id="pagination-links" class="col-md col align-items-center"
                data-url="{{ route('kalibrasi.admin.master-lists.search') }}">
                {{-- Generate by AJAX --}}
            </div>

            <div class="col-auto">
                <form id="exportForm" method="GET" action="{{ route('kalibrasi.admin.master-lists.export') }}">
                    <input type="hidden" name="keyword" id="export-keyword">
                    <input type="hidden" name="format" id="export-format">
                    <button type="button" class="btn btn-success btn-sm me-2" id="btn-export-excel">
                        Export Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-export-pdf">
                        Export PDF
                    </button>
                </form>
            </div>
            <div class="col-auto">
                <a href="{{ route('dashboard', ['key' => 'master-data', 'application' => 'KALIBRASI']) }}"
                    class="btn btn-primary btn-sm">Close</a>
            </div>
        </div>
    </div>
    </div>
    <!-- Modal Tambah/Edit MasterList -->
    <div class="modal fade" id="masterlistModal" tabindex="-1" aria-labelledby="masterlistModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" id="masterlistForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="masterlistModalLabel">Add Masterlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-sm mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control form-control-sm" id="id-num" placeholder="ID Num"
                                name="id_num" disabled>
                            <label for="id-num">ID Num</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control form-control-sm" id="sn-num" placeholder="SN Num"
                                name="sn_num" required>
                            <label for="sn-num">SN Num</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="capacity" placeholder="Capacity" name="capacity"
                                required>
                            <label for="capacity">Capacity</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="accuracy" placeholder="Accuracy" name="accuracy"
                                required>
                            <label for="accuracy">Accuracy</label>
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="id-unit" name="unit_id" required>
                                <option value="" disabled>Choose...</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->symbol }} -
                                        {{ $unit->name }}</option>
                                @endforeach
                            </select>
                            <label for="id-unit" class="form-label">Unit</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="brand" placeholder="Brand"
                                name="brand" required>
                            <label for="brand">Brand</label>
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="calibration-type" name="calibration_type" required>
                                <option value="" disabled>Choose...</option>
                                <option value="External">External</option>
                                <option value="Internal">Internal</option>
                            </select>
                            <label for="calibration-type" class="form-label">Calibration Type</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="first-used" placeholder="First Used"
                                name="first_used" required>
                            <label for="first-used">1st Used</label>
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="rank" name="rank" required>
                                <option value="">Choose...</option>
                                <option value="AA">AA</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                            <label for="rank" class="form-label">Rank</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" class="form-control" id="calibration-freq"
                                placeholder="Calibration Freq" name="calibration_freq" required>
                            <label for="calibration-freq">Calibration Freq</label>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="acceptance-criteria" placeholder="Acceptance Criteria" name="acceptance_criteria"
                            required></textarea>
                        <label for="acceptance-criteria">Acceptance Criteria</label>
                    </div>
                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="pic" placeholder="PIC" name="pic"
                                required>
                            <label for="pic">PIC</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="location" placeholder="Location"
                                name="location" required>
                            <label for="location">Location</label>
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

    <!-- Modal Delete MasterList -->
    <div class="modal fade" id="deleteMasterListModal" tabindex="-1" aria-labelledby="deleteMasterListModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteMasterListForm" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMasterListModalLabel">Delete MasterList</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the masterlist <strong id="deleteMasterListName"></strong>?
                    </p>
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
        function fetchMasterLists(keyword = '', page = 1) {
            $('#loading').show();
            $.ajax({
                url: `{{ route('kalibrasi.admin.master-lists.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                    page: page
                },
                success: function(response) {
                    $('#master-list-table-body').html(response.html);
                    $('#pagination-links').html(response.pagination);
                    $('html, body').animate({
                        scrollTop: $('#master-list-table').offset().top - 100
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
            // Delegasi tombol Edit
            $(document).on('click', '.btn-edit-masterlist', function() {
                const id = $(this).data('id');
                $('#master-list-id').val(id);
                $('#id-num').val($(this).data('id-num'));
                $('#sn-num').val($(this).data('sn-num'));
                $('#capacity').val($(this).data('capacity'));
                $('#accuracy').val($(this).data('accuracy'));
                $('#id-unit').val($(this).data('id-unit'));
                $('#brand').val($(this).data('brand'));
                $('#calibration-type').val($(this).data('calibration-type'));
                $('#first-used').val($(this).data('first-used'));
                $('#rank').val($(this).data('rank'));
                $('#calibration-freq').val($(this).data('calibration-freq'));
                $('#acceptance-criteria').val($(this).data('acceptance-criteria'));
                $('#pic').val($(this).data('pic'));
                $('#location').val($(this).data('location'));
                $('#masterlistForm').attr('action',
                    `{{ url('kalibrasi/admin/master-lists/update-master-list') }}/${id}`);
                new bootstrap.Modal(document.getElementById('masterlistModal')).show();
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
            $('#search-master-list').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchMasterLists(keyword);
                }, 400);
            });

            // AJAX pagination
            $(document).on('click', '#pagination-links .pagination a', function(e) {
                e.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                const keyword = $('#search-master-list').val();
                fetchMasterLists(keyword, page);
            });

            // Export current search results as Excel or PDF.
            $(document).on('click', '#btn-export-excel, #btn-export-pdf', function() {
                const format = $(this).is('#btn-export-excel') ? 'excel' : 'pdf';
                $('#export-keyword').val($('#search-master-list').val() || '');
                $('#export-format').val(format);

                // Build URL and open in new tab so file downloads without disrupting the page
                const $form = $('#exportForm');
                const url = $form.attr('action') + '?' + $form.serialize();
                window.open(url, '_blank');
            });

            // Initial fetch
            fetchMasterLists();
        });
    </script>
@endsection
