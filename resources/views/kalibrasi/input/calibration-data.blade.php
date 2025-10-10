@extends('layouts.app')

@section('styles')
    <style>
        .selected-row {
            background-color: #e2f0ff !important;
        }

        #certificateContent iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        #certificateContent iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
@endsection
@section('content')
    <div class="container mt-1 mt-md-3">
        <form method="POST" class="needs-validation" enctype="multipart/form-data" id="calibration-form"
            action="{{ route('kalibrasi.store.calibration') }}">
            @csrf
            @if ($pending)
                <input type="hidden" name="master_list_id" value="{{ $pending->master_list_id }}">
            @endif
            <input type="hidden" id="id-result" name="id" value="{{ old('id') ?? '' }}">
            @if ($pending)
                <input type="hidden" name="master_list_id" value="{{ $pending->master_list_id }}">
            @endif
            <input type="hidden" id="id-result" name="id" value="{{ old('id') ?? '' }}">
            <div class="row justify-content-center mb-1">
                <div class="col-md-6 ps-0 pe-1">
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-primary text-light width-label-1">ID / SN Number</span>
                        <input type="text" aria-label="No ID" placeholder="-"
                            class="form-control text-center @error('id_num') is-invalid @enderror {{ old('id_num') ? 'is-valid' : '' }}"
                            name="id_num" id="id-num" autocomplete="off" maxlength="7" required
                            value="{{ old('id_num') ?? $pending?->masterList->id_num }}"
                            {{ old('id_num') ? '' : 'autofocus' }} name="id_num" id="id-num" autocomplete="off"
                            maxlength="7" required value="{{ old('id_num') ?? $pending?->masterList->id_num }}"
                            {{ old('id_num') ? '' : 'autofocus' }}>
                        <input type="text" aria-label="No SN" placeholder="No SN"
                            class="form-control text-center width-label-1" id="sn-num" disabled>
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <label class="input-group-text bg-primary text-light width-label-1"
                            for="calibration-date">Calibration
                            Date</label>
                        <input type="date" aria-label="Date Now" placeholder="Date Now"
                            class="form-control width-label-1 @error('calibration_date') is-invalid @enderror"
                            id="calibration-date" name="calibration_date"
                            value="{{ old('calibration_date') ?? now()->toDateString() }}"
                            max="{{ now()->toDateString() }}">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-primary text-light width-label-1">Equipment Name</span>
                        <input type="text" class="form-control" placeholder="auto" id="equipment-name" disabled>
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-primary text-light width-label-1">Capacity</span>
                        <input type="text" class="form-control" placeholder="auto" id="capacity"
                            aria-describedby="capacity" disabled>
                        <span class="input-group-text bg-primary text-light">Accuracy ±</span>
                        <input type="number" class="form-control" placeholder="auto" id="accuracy" disabled>
                        <span class="input-group-text bg-primary text-light justify-content-center" id="unit"></span>
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-primary text-light width-label-1">Brand</span>
                        <input type="text" class="form-control" id="brand" placeholder="auto" disabled>
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-primary text-light width-label-1">Location - PIC</span>
                        <input type="text" id="location" class="form-control" placeholder="auto" disabled>
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-primary text-light width-label-1">Calibration Type</span>
                        <input type="text" class="form-control calibration-type" placeholder="Internal / External" hidden
                            name="calibration_type">
                        <input type="text" class="form-control calibration-type" placeholder="Internal / External"
                            disabled>
                    </div>
                </div>
                <div class="col-md-6 ps-1 pe-0">
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-primary text-light width-label-2">Standar
                            Keberterimaan</span>
                        <input type="text" class="form-control" id="acceptance-criteria"
                            placeholder="Kg / gr / °C / mm" disabled>
                    </div>
                    <div class="input-group input-group-sm mb-1" id="calibrator-equipment-section">
                        <span class="input-group-text bg-primary text-light width-label-2">Calibrator Equipment</span>
                        <select type="text"
                            class="form-select @error('calibrator_equipment') is-invalid @enderror {{ old('calibrator_equipment') ? 'is-valid' : '' }}"
                            id="calibrator-equipment" name="calibrator_equipment" required>
                            <option {{ old('calibrator_equipment') ? '' : 'selected' }}>Choose...</option>
                        </select>
                    </div>
                    <div class="row g-1">
                        <div class="col">
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter 1</span>
                                <input type="text" class="form-control" id="std-1" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_01') is-invalid @enderror {{ old('param_01') ? 'is-valid' : '' }}"
                                    id="act-1" name="param_01" placeholder="act" step="0.00001"
                                    value="{{ old('param_01') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter 2</span>
                                <input type="text" class="form-control" id="std-2" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_02') is-invalid @enderror {{ old('param_02') ? 'is-valid' : '' }}"
                                    id="act-2" name="param_02" placeholder="act" step="0.00001"
                                    value="{{ old('param_02') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter 3</span>
                                <input type="text" class="form-control" id="std-3" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_03') is-invalid @enderror {{ old('param_03') ? 'is-valid' : '' }}"
                                    id="act-3" name="param_03" placeholder="act" step="0.00001"
                                    value="{{ old('param_03') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter 4</span>
                                <input type="text" class="form-control" id="std-4" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_04') is-invalid @enderror {{ old('param_04') ? 'is-valid' : '' }}"
                                    id="act-4" name="param_04" placeholder="act" step="0.00001"
                                    value="{{ old('param_04') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter 5</span>
                                <input type="text" class="form-control" id="std-5" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_05') is-invalid @enderror {{ old('param_05') ? 'is-valid' : '' }}"
                                    id="act-5" name="param_05" placeholder="act" step="0.00001"
                                    value="{{ old('param_05') }}">
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter &nbsp; 6</span>
                                <input type="text" class="form-control" id="std-6" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_06') is-invalid @enderror {{ old('param_06') ? 'is-valid' : '' }}"
                                    id="act-6" name="param_06" placeholder="act" step="0.00001"
                                    value="{{ old('param_06') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter &nbsp; 7</span>
                                <input type="text" class="form-control" id="std-7" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_07') is-invalid @enderror {{ old('param_07') ? 'is-valid' : '' }}"
                                    id="act-7" name="param_07" placeholder="act" step="0.00001"
                                    value="{{ old('param_07') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter &nbsp; 8</span>
                                <input type="text" class="form-control" id="std-8" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_08') is-invalid @enderror {{ old('param_08') ? 'is-valid' : '' }}"
                                    id="act-8" name="param_08" placeholder="act" step="0.00001"
                                    value="{{ old('param_08') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter &nbsp; 9</span>
                                <input type="text" class="form-control" id="std-9" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_09') is-invalid @enderror {{ old('param_09') ? 'is-valid' : '' }}"
                                    id="act-9" name="param_09" placeholder="act" step="0.00001"
                                    value="{{ old('param_09') }}">
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Parameter 10</span>
                                <input type="text" class="form-control" id="std-10" placeholder="std" disabled>
                                <input type="number"
                                    class="form-control @error('param_10') is-invalid @enderror {{ old('param_10') ? 'is-valid' : '' }}"
                                    id="act-10" name="param_10" placeholder="act" step="0.00001"
                                    value="{{ old('param_10') }}">
                            </div>
                        </div>
                        <div class="col-md-2 align-content-center">
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Judgement</span>
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <select
                                    class="form-select @error('judgement') is-invalid @enderror {{ old('judgement') ? 'is-valid' : '' }}"
                                    id="judgement" name="judgement" required>
                                    <option value="" {{ old('judgement') == '' ? 'selected' : '' }}>Choose...
                                    </option>
                                    <option value="OK" {{ old('judgement') == 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="NG" {{ old('judgement') == 'NG' ? 'selected' : '' }}>NG</option>
                                    <option value="Disposal" {{ old('judgement') == 'Disposal' ? 'selected' : '' }}>
                                        Disposal
                                    </option>
                                </select>
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text bg-primary text-light">Created By</span>
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <input type="text" class="form-control" id="created_by" disabled
                                    value="{{ auth()->user()->name }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row table-responsive mb-3" style="max-height: 85px; overflow-y: auto;">
                <table class="table table-sm table-bordered align-middle text-nowrap">
                    <thead class="table-primary sticky-top">
                        <tr class="align-middle text-center">
                            <th scope="col">No
                            </th>
                            <th scope="col">Calibration Date</th>
                            <th scope="col">Nomor ID/SN</th>
                            <th scope="col">Equipment Name</th>
                            <th scope="col">Capacity</th>
                            <th scope="col">Accuracy</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Location</th>
                            <th scope="col">PIC</th>
                            <th scope="col">Calibration Type</th>
                            <th scope="col">Rank</th>
                            <th scope="col">Calibration Freq</th>
                            <th scope="col">Acceptance Criteria</th>
                            <th scope="col">Judgement</th>
                            <th scope="col">Certificate</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        @foreach ($results as $result)
                            <tr>
                                <td><button type="button"
                                        class="btn btn-primary btn-select-id {{ $pending ? 'disabled' : '' }}"
                                        data-num="{{ $result->id }}"
                                        data-id="{{ $result->id_num }}">{{ $loop->iteration }}</button></td>
                                <td> {{ $result->calibration_date->format('d-m-Y') }} </td>
                                <td> {{ $result->id_num }} / {{ $result->masterList->sn_num }} </td>
                                <td>
                                    @isset($result->masterList->equipment->name)
                                        {{ $result->masterList->equipment->name }}
                                    @else
                                        <span class="text-danger">has been deleted</span>
                                    @endisset
                                </td>
                                <td>
                                    {{ $result->masterList->capacity }}
                                    @isset($result->masterList->unit->symbol)
                                        {{ $result->masterList->unit->symbol }}
                                    @else
                                        <span class="text-danger">N/A</span>
                                    @endisset
                                </td>
                                <td>
                                    ± {{ $result->masterList->accuracy }}
                                    @isset($result->masterList->unit->symbol)
                                        {{ $result->masterList->unit->symbol }}
                                    @else
                                        <span class="text-danger">N/A</span>
                                    @endisset
                                </td>
                                <td> {{ $result->masterList->brand }} </td>
                                <td> {{ $result->masterList->location }} </td>
                                <td> {{ $result->masterList->pic }} </td>
                                <td> {{ $result->masterList->calibration_type }} </td>
                                <td> {{ $result->masterList->rank }} </td>
                                <td> {{ $result->masterList->calibration_freq }} </td>
                                <td> {{ $result->masterList->acceptance_criteria }} </td>
                                <td> {{ $result->judgement }} </td>
                                <td>
                                    @if ($result->certificate)
                                        <button type="button" class="btn btn-primary btn-view-certificate"
                                            data-bs-toggle="modal" data-bs-target="#certificateModal"
                                            data-path="{{ asset('storage/' . $result->certificate) }}"
                                            data-ext="{{ pathinfo($result->certificate, PATHINFO_EXTENSION) }}">
                                            Show
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if ($results->isEmpty())
                            <tr>
                                <td colspan="15" class="text-center">No data available</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="row justify-content-end">
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <a href="{{ route('dashboard', ['key' => 'menu-input', 'application' => 'KALIBRASI']) }}"
                        class="btn btn-primary {{ $pending ? 'disabled' : '' }}">Close</a>
                </div>
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <button type="button" class="btn btn-primary {{ $pending ? 'disabled' : '' }}"
                        data-bs-toggle="modal" data-bs-target="#resultModal">Show All Data</button>
                </div>
                <div class="col-6 col-md-auto text-center mb-1 mb-md-0">
                    <button type="button" id="print" class="btn btn-primary" disabled>Print Label</button>
                </div>
                <div class="col-12 col-md-auto text-center mb-1 mb-md-0">
                    <input class="form-control form-control-sm" id="certificate" name="certificate" type="file"
                        accept=".pdf" hidden onchange="updateCertificateLabel(this)">
                    <button type="button" class="btn btn-primary" id="certificate-label"
                        onclick="document.getElementById('certificate').click()" disabled>Upload Calibration External
                        Certificate</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
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
                                <th scope="col">Calibration Date</th>
                                <th scope="col">ID/SN Number</th>
                                <th scope="col">Equipment Name</th>
                                <th scope="col">Capacity</th>
                                <th scope="col">Accuracy</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Location</th>
                                <th scope="col">PIC</th>
                                <th scope="col">Calibration Type</th>
                                <th scope="col">Rank</th>
                                <th scope="col">Calibration Freq</th>
                                <th scope="col">Acceptance Criteria</th>
                                <th scope="col">Judgement</th>
                                <th scope="col">Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $result)
                                <tr>
                                    <td><button class="btn btn-primary btn-select-id" data-num="{{ $result->id }}"
                                            data-id="{{ $result->id_num }}"
                                            data-bs-dismiss="modal">{{ $loop->iteration }}</button></td>
                                    <td>{{ $result->calibration_date->format('d-m-Y') }}</td>
                                    <td>{{ $result->id_num }} / {{ $result->masterList->sn_num }}</td>
                                    <td>{{ $result->masterList->equipment->name ?? '' }}</td>
                                    <td>
                                        {{ $result->masterList->capacity }}
                                        @isset($result->masterList->unit->symbol)
                                            {{ $result->masterList->unit->symbol }}
                                        @else
                                            <span class="text-danger">N/A</span>
                                        @endisset
                                    </td>
                                    <td>
                                        ± {{ $result->masterList->accuracy }}
                                        @isset($result->masterList->unit->symbol)
                                            {{ $result->masterList->unit->symbol }}
                                        @else
                                            <span class="text-danger">N/A</span>
                                        @endisset
                                    </td>
                                    <td>{{ $result->masterList->brand }}</td>
                                    <td>{{ $result->masterList->location }}</td>
                                    <td>{{ $result->masterList->pic }}</td>
                                    <td>{{ $result->masterList->calibration_type }}</td>
                                    <td>{{ $result->masterList->rank }}</td>
                                    <td>{{ $result->masterList->calibration_freq }}</td>
                                    <td>{{ $result->masterList->acceptance_criteria }}</td>
                                    <td>{{ $result->judgement }}</td>
                                    <td>
                                        @if ($result->certificate)
                                            <button type="button" class="btn btn-primary btn-view-certificate"
                                                data-bs-toggle="modal" data-bs-target="#certificateModal"
                                                data-path="{{ asset('storage/' . $result->certificate) }}"
                                                data-ext="{{ pathinfo($result->certificate, PATHINFO_EXTENSION) }}">
                                                Show
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="certificateModal" tabindex="-1" aria-labelledby="certificateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between">
                    <h5 class="modal-title">Calibration Certificate</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printCertificate()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>
                <div class="modal-body text-center vh-100" id="certificateContent">
                    <div class="text-muted">Loading...</div>
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
            if ($('#id-result').val() !== '') {
                $('#calibration-form').attr('action',
                    `{{ url('/kalibrasi/input/calibration-data') }}/${$('#id-result').val()}`);
            }
            currentAjax = $.ajax({
                url: `{{ url('/kalibrasi/get-masterlist') }}/${idNum}`,
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                success: function(data) {
                    $('#sn-num').val(data.sn_num);
                    $('#equipment-name').val(data.equipment_name);
                    $('#capacity').val(data.capacity);
                    $('#accuracy').val(data.accuracy);
                    $('#unit').text(data.unit);
                    $('#brand').val(data.brand);
                    $('#location').val(data.location + ' - ' + data.pic);
                    $('.calibration-type').val(data.calibration_type);
                    $('#acceptance-criteria').val(data.acceptance_criteria);
                    if (data.calibration_type === 'External') {
                        $('#calibrator-equipment-section').addClass('d-none');
                        $('#calibrator-equipment').prop('required', false).prop('disabled', true)
                            .hide();
                        $('#certificate').next('button').prop('required', true).prop('disabled', false).show();
                    } else {
                        $('#calibrator-equipment').empty();
                        $('#calibrator-equipment').append(
                            `<option disabled {{ old('calibrator_equipment') ? '' : 'selected' }}>Choose...</option>`
                        );
                        data.calibrator_equipments.forEach(function(item) {
                            $('#calibrator-equipment').append(
                                `<option value="${item.id_num}" ${item.id_num == "{{ old('calibrator_equipment') }}" ? 'selected' : ''}>${item.id_num} - ${item.equipment_name}</option>`
                            );
                        });
                        // Pastikan nilai select sesuai:
                        const currentVal =
                            "{{ old('calibrator_equipment') ?? '' }}";
                        if (currentVal) {
                            $('#calibrator-equipment').val(currentVal);
                        }
                        $('#calibrator-equipment-section').removeClass('d-none');
                        $('#calibrator-equipment').prop('required', true).prop('disabled', false)
                            .show();
                        $('#certificate').next('button').prop('required', false).prop('disabled', true).hide();
                        $('#certificate').val('');

                    }
                    // Tambahan di sini:
                    if ($('#id-result').val()) {
                        fetchActualValue($('#id-result').val());
                    }
                    for (let i = 1; i <= 10; i++) {
                        const key = 'param_' + String(i).padStart(2, '0');
                        const stdValue = data.standard[key];
                        const stdInput = $('#std-' + i);
                        const actInput = $('#act-' + i);

                        if (stdValue == 99999) {
                            stdInput.val('-');
                            actInput.prop('required', false);
                            actInput.prop('disabled', true).val(''); // nonaktif dan kosongkan input
                        } else {
                            stdInput.val(stdValue);
                            actInput.prop('required', true);
                            actInput.prop('disabled', false); // aktifkan kembali kalau perlu
                        }
                    }
                    $('#print').prop('disabled', false);
                },
                error: function(xhr) {
                    if (xhr.status !== 0) {
                        // tampilkan error hanya jika bukan karena abort
                        $('#sn-num').val('Data Tidak Ditemukan');
                        $('#equipment-name').val('');
                        $('#capacity').val('');
                        $('#accuracy').val('');
                        $('#unit').text('');
                        $('#brand').val('');
                        $('#location').val('');
                        $('.calibration-type').val('');
                        $('#acceptance-criteria').val('');
                        $('#calibrator-equipment').empty('');
                        $('#calibrator-equipment').append(
                            `<option disabled selected>Choose...</option>`);
                        $('#certificate').val('');
                        for (let i = 1; i <= 10; i++) {
                            $('#std-' + i).val('');
                        }
                        $('#print').prop('disabled', true);
                    }
                }
            });
        }

        function fetchActualValue(id) {
            $.ajax({
                url: `{{ url('/kalibrasi/get-actual-value') }}/${id}`,
                method: 'GET',
                success: function(data) {
                    $('#calibration-date').val(new Date(data.calibration_date).toISOString().split('T')[0]);
                    $('#calibrator-equipment').val(data.calibrator_equipment).change();
                    $('#judgement').val(data.judgement);
                    $('#created_by').val(data.created_by).attr('disabled', true);

                    for (let i = 1; i <= 10; i++) {
                        const key = 'param_' + String(i).padStart(2, '0');
                        const value = parseFloat(data[key]);
                        const actInput = $('#act-' + i);

                        if (value == 0.00) {
                            actInput.val('').prop('disabled', true);
                        } else {
                            actInput.val(value).prop('disabled', false);
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status !== 0) {
                        $('#id-result').val('');
                        $('#calibration-date').val('');
                        $('#calibrator-equipment').val('');
                        $('#judgement').val('');
                        $('#created_by').val(@json(auth()->user()->name)).attr('disabled', true);

                        for (let i = 1; i <= 10; i++) {
                            $('#act-' + i).val('').prop('disabled', false);
                        }
                    }
                }
            });
        }

        $input.on('input', function() {
            const val = $(this).val().trim();
            clearTimeout(debounceTimer);

            // validasi minimal 7 karakter
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

        $(document).on('click', '.btn-select-id', function() {
            const selectedId = $(this).data('id');
            const num = $(this).data('num');

            $('#id-result').val(num);

            // trigger autofill
            $('#id-num').val(selectedId).trigger('input');

            // highlight baris terpilih
            $('.table tbody tr').removeClass('selected-row'); // reset
            $(this).closest('tr').addClass('selected-row'); // tandai baris aktif

            $('#calibration-form').attr('action',
                `{{ url('/kalibrasi/input/calibration-data') }}/${num}`);
        });


        // Optional: search bar real-time di modal
        $('#search-table').on('input', function() {
            const keyword = $(this).val().toLowerCase();
            $('#modal-table tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.includes(keyword));
            });
        });

        $(document).on('click', '.btn-view-certificate', function() {
            const path = $(this).data('path');
            const ext = $(this).data('ext').toLowerCase();
            const container = $('#certificateContent');

            container.html('<div class="text-muted">Loading...</div>'); // reset dulu

            if (ext === 'pdf') {
                container.html(`<iframe loading="lazy" src="${path}"></iframe>`);
            } else {
                container.html(`<div class="text-danger">Format file tidak dikenali: .${ext}</div>`);
            }
        });

        $('#print').on('click', function() {
            const idNum = $('#id-num').val();
            if (idNum) {
                window.open(`/print-label/${idNum}`, '_self');
            }
        });

        // focus pada input ID atau calibration date maka ubah action form jadi insert
        $('#id-num').on('focus', function() {
            $('#id-result').val('');
            $('#calibration-form').attr('action', "{{ route('kalibrasi.store.calibration') }}");
        });

        $(document).ready(function() {
            if ($input.val().trim().length === 7) {
                fetchMasterList($input.val().trim());
            }
        });
    </script>
    <script>
        function updateCertificateLabel(input) {
            const fileName = input.files[0]?.name || 'Upload Calibration External Certificate';
            document.getElementById('certificate-label').textContent = fileName;
            const certificateLabel = document.getElementById('certificate-label');
            certificateLabel.classList.remove('btn-primary');
            certificateLabel.classList.add('btn-success');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const tag = e.target.tagName.toLowerCase();
                // Hanya submit kalau fokus di input atau select (bukan button/table/etc)
                if (['input', 'textarea', 'button', 'select'].includes(tag)) {
                    const form = document.querySelector('.needs-validation');
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        const firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.reportValidity();
                        }
                    }
                }
            }
        });

        function printCertificate() {
            const iframe = document.querySelector('#certificateContent iframe');
            if (iframe && iframe.src) {
                const printWindow = window.open(iframe.src, '_blank');
                // Tidak semua browser bisa langsung print PDF, tapi bisa diarahkan ke preview cetak
            } else {
                alert('Tidak ada sertifikat untuk dicetak.');
            }
        }
    </script>

    @if ($pending)
        <script>
            // Cegah back dan refresh
            history.pushState(null, null, location.href);
            window.onpopstate = () => history.pushState(null, null, location.href);
        </script>
    @endif
@endsection
