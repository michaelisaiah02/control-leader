@extends('layouts.app')

@section('styles')
    <style>
        textarea {
            height: 25px;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-1 mt-md-3">
        <form method="POST" id="repair-form">
            @csrf
            <input type="hidden" name="form_type" id="form-type" value="{{ old('form_type') ?? 'save' }}">
            <input type="hidden" name="id" id="repair-id" value="{{ old('id') }}">
            <div class="row justify-content-center">
                <div class="col-md-6 ps-0 pe-1">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">ID / SN Number</span>
                        <input type="text" aria-label="ID Num" placeholder="-"
                            class="form-control text-center @error('id_num') is-invalid @enderror {{ old('id_num') ? 'is-valid' : '' }}"
                            name="id_num" id="id-num" value="{{ old('id_num') }}" autocomplete="id_num" required>
                        <input type="text" aria-label="SN Num" placeholder="SN Num" class="form-control w-25 text-center"
                            id="sn-num" disabled>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text bg-primary text-light" for="equipment-name">Equipment Name</label>
                        <input type="text" aria-label="SN Num" placeholder="NEW ALAT UKUR"
                            class="form-control w-25 text-center" id="equipment-name" disabled>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Capacity</span>
                        <input type="text" class="form-control" placeholder="auto" id="capacity"
                            aria-describedby="capacity" disabled>
                        <span class="input-group-text bg-primary text-light" id="unit"></span>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Brand</span>
                        <input type="text" class="form-control" id="brand" placeholder="auto" disabled>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Problem Date</span>
                        <input type="date"
                            class="form-control @error('problem_date') is-invalid @enderror {{ old('problem_date') ? 'is-valid' : '' }}"
                            id="problem-date" name="problem_date" value="{{ old('problem_date') }}"
                            max="{{ now()->toDateString() }}" required>
                        <span class="input-group-text bg-primary text-light">Repair Date</span>
                        <input type="date"
                            class="form-control @error('repair_date') is-invalid @enderror {{ old('repair_date') ? 'is-valid' : '' }}"
                            id="repair-date" name="repair_date" value="{{ old('repair_date') ?? now()->format('Y-m-d') }}"
                            max="{{ now()->toDateString() }}" required>
                    </div>
                </div>
                <div class="col-md-6 ps-1 pe-0">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">PIC</span>
                        <input type="text" class="form-control" id="pic" placeholder="Name" disabled>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Location</span>
                        <input type="text" class="form-control" id="location" placeholder="auto" disabled>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Problem</span>
                        <textarea class="form-control @error('problem') is-invalid @enderror {{ old('problem') ? 'is-valid' : '' }}"
                            id="problem" name="problem" required>{{ old('problem') }}</textarea>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Countermeasure</span>
                        <textarea
                            class="form-control @error('countermeasure') is-invalid @enderror {{ old('countermeasure') ? 'is-valid' : '' }}"
                            id="countermeasure" name="countermeasure" required>{{ old('countermeasure') }}</textarea>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Judgement</span>
                        <select
                            class="form-select @error('judgement') is-invalid @enderror {{ old('judgement') ? 'is-valid' : '' }}"
                            id="judgement" name="judgement" required>
                            <option disabled {{ old('judgement') ? '' : 'selected' }}>OK / NG / Disposal</option>
                            <option value="OK" {{ old('judgement') === 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="NG" {{ old('judgement') === 'NG' ? 'selected' : '' }}>NG</option>
                            <option value="Disposal" {{ old('judgement') === 'Disposal' ? 'selected' : '' }}>Disposal
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row table-responsive mb-3" style="max-height: 85px; overflow-y: auto;">
                <table class="table table-sm table-bordered align-middle text-nowrap">
                    <thead class="table-primary sticky-top">
                        <tr class="align-middle text-center">
                            <th scope="col">No</th>
                            <th scope="col">No ID</th>
                            <th scope="col">Equipment Name</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Problem Date</th>
                            <th scope="col">Repair Date</th>
                            <th scope="col">Location</th>
                            <th scope="col">Problem</th>
                            <th scope="col">Countermeasure</th>
                            <th scope="col">Judgement</th>
                            <th scope="col">PIC Repair</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        @foreach ($repairs as $repair)
                            <tr>
                                <td><button type="button" class="btn btn-primary btn-select-id"
                                        data-num="{{ $repair->id }}"
                                        data-id="{{ $repair->id_num }}">{{ $loop->iteration }}</button></td>
                                <td>{{ $repair->id_num }}</td>
                                <td>{{ $repair->masterList->equipment->name }}</td>
                                <td>{{ $repair->masterList->brand }}</td>
                                <td>{{ $repair->problem_date->format('j M Y') }}</td>
                                <td>{{ $repair->repair_date->format('j M Y') }}</td>
                                <td>{{ $repair->masterList->location }}</td>
                                <td>{{ $repair->problem }}</td>
                                <td>{{ $repair->countermeasure }}</td>
                                <td>{{ $repair->judgement }}</td>
                                <td>{{ $repair->pic_repair }}</td>
                            </tr>
                        @endforeach
                        @if ($repairs->isEmpty())
                            <tr>
                                <td colspan="10" class="text-center">No data available</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="row justify-content-end">
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <a href="{{ route('dashboard', ['key' => 'menu-input', 'application' => 'KALIBRASI']) }}"
                        class="btn btn-primary">Close</a>
                </div>
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#repairModal">Show All Data</button>
                </div>
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <a class="btn btn-primary disabled" id="print">Print Report</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Repair Modal -->
    <div class="modal fade" id="repairModal" tabindex="-1" aria-labelledby="repairModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Calibration Data</h5>
                    <input type="text" class="form-control w-25 ms-3" placeholder="Cari..." id="search-table">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-0 m-0 table-responsive">
                    <table class="table table-sm table-bordered align-middle text-nowrap" id="modal-table">
                        <thead class="table-primary sticky-top">
                            <tr class="align-middle text-center">
                                <th scope="col">No</th>
                                <th scope="col">No ID</th>
                                <th scope="col">Equipment Name</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Problem Date</th>
                                <th scope="col">Repair Date</th>
                                <th scope="col">Location</th>
                                <th scope="col">Problem</th>
                                <th scope="col">Countermeasure</th>
                                <th scope="col">Judgement</th>
                                <th scope="col">PIC Repair</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            @foreach ($repairs as $repair)
                                <tr>
                                    <td><button type="button" class="btn btn-primary btn-select-id"
                                            data-num="{{ $repair->id }}" data-id="{{ $repair->id_num }}"
                                            data-bs-dismiss="modal">{{ $loop->iteration }}</button></td>
                                    <td>{{ $repair->id_num }}</td>
                                    <td>{{ $repair->masterList->equipment->name }}</td>
                                    <td>{{ $repair->masterList->brand }}</td>
                                    <td>{{ $repair->problem_date->format('j M Y') }}</td>
                                    <td>{{ $repair->repair_date->format('j M Y') }}</td>
                                    <td>{{ $repair->masterList->location }}</td>
                                    <td>{{ $repair->problem }}</td>
                                    <td>{{ $repair->countermeasure }}</td>
                                    <td>{{ $repair->judgement }}</td>
                                    <td>{{ $repair->pic_repair }}</td>
                                </tr>
                            @endforeach
                            @if ($repairs->isEmpty())
                                <tr>
                                    <td colspan="10" class="text-center">No data available</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        const $input = $('#id-num');
        let debounceTimer;
        let currentAjax = null;

        function fetchMasterList(idNum) {
            // Cancel request sebelumnya kalau masih berjalan
            if (currentAjax) currentAjax.abort();

            currentAjax = $.ajax({
                url: `/kalibrasi/get-masterlist/${idNum}`,
                method: 'GET',
                success: function(data) {
                    $('#sn-num').val(data.sn_num);
                    $('#equipment-name').val(data.equipment_name);
                    $('#capacity').val(data.capacity);
                    $('#unit').text(data.unit);
                    $('#brand').val(data.brand);
                    $('#location').val(data.location);
                    $('#pic').val(data.pic);

                    $('#print').attr('href', `/print-report-repair/${idNum}`);
                    $('#print').removeClass('disabled');
                },
                error: function(xhr) {
                    if (xhr.status !== 0) {
                        // tampilkan error hanya jika bukan karena abort
                        $('#sn-num').val('Data Tidak Ditemukan');
                        $('#equipment-name').val('');
                        $('#capacity').val('');
                        $('#unit').text('');
                        $('#brand').val('');
                        $('#pic').val('');
                        $('#location').val('');

                        $('#print').attr('href', '#');
                        $('#print').removeClass('disabled');
                    }
                }
            });
        }

        function fetchRepairData(id) {
            $.ajax({
                url: `/get-repair-data/${id}`,
                method: 'GET',
                success: function(data) {
                    $('#problem').val(data.problem);
                    $('#countermeasure').val(data.countermeasure);
                    $('#judgement').val(data.judgement);
                    $('#problem-date').val(data.problem_date);
                    $('#repair-date').val(data.repair_date);
                },
                error: function(xhr) {
                    if (xhr.status !== 0) {
                        // tampilkan error hanya jika bukan karena abort
                        $('#problem').val('Data Tidak Ditemukan');
                        $('#countermeasure').val('');
                        $('#judgement').val('');
                        $('#problem-date').val('');
                        $('#repair-date').val('');
                    }
                }
            });
        }

        $input.on('input', function() {
            const val = $(this).val().trim();
            clearTimeout(debounceTimer);

            // validasi minimal 5 karakter misalnya, atau harus ada dash kayak TIM-001
            if (val.length < 6) return;

            debounceTimer = setTimeout(() => {
                fetchMasterList(val);
            }, 500); // hanya tunggu setengah detik, cukup responsif dan aman
        });

        // Deteksi Enter atau blur sebagai final trigger juga
        $input.on('blur keydown', function(e) {
            if (e.type === 'blur' || e.key === 'Enter') {
                const val = $(this).val().trim();
                if (val.length >= 6) {
                    clearTimeout(debounceTimer);
                    fetchMasterList(val);
                }
            }
        });

        $(document).ready(function() {
            if ($input.val().trim().length === 7) {
                fetchMasterList($input.val().trim());
            }
            // jika ada error cek kembali apakah tipe formnya save atau edit
            if ($('#form-type').val() === 'edit') {
                const num = $('#repair-id').val();
                $('#repair-form').attr('action',
                    `{{ url('/kalibrasi/input/repair-data') }}/${num}`);
            } else {
                $('#repair-form').attr('action', "{{ route('kalibrasi.store.repair') }}");
            }
        })

        $(document).on('click', '.btn-select-id', function() {
            const selectedId = $(this).data('id');
            const num = $(this).data('num');

            // trigger autofill
            $('#id-num').val(selectedId).trigger('input');

            // highlight baris terpilih
            $('.table tbody tr').removeClass('selected-row'); // reset
            $(this).closest('tr').addClass('selected-row'); // tandai baris aktif

            $('#form-type').val('edit');
            $('#repair-id').val(num);
            $('#repair-form').attr('action',
                `{{ url('/kalibrasi/input/repair-data') }}/${num}`);
            fetchRepairData(num);
        });


        // Optional: search bar real-time di modal
        $('#search-table').on('input', function() {
            const keyword = $(this).val().toLowerCase();
            $('#modal-table tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.includes(keyword));
            });
        });

        $('#id-num').on('focus', function() {
            $('#form-type').val('save');
            $('#repair-id').val('');
            $('#repair-form').attr('action', "{{ route('kalibrasi.store.repair') }}");
        });
    </script>
@endsection
