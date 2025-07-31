@extends('layouts.app')

@section('styles')
    <style>
        #id-num::placeholder {
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-1 mt-md-3">
        <form action="{{ route('kalibrasi.store.equipment') }}" method="POST">
            @csrf
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <label class="input-group-text bg-primary text-light" for="equipment_name">Equipment Name</label>
                        <select
                            class="form-select @error('type_id') is-invalid @enderror {{ old('type_id') ? 'is-valid' : '' }}"
                            id="equipment_name" name="type_id" required>
                            <option value="" disabled {{ old('type_id') ? '' : 'selected' }}>Choose...</option>
                            @foreach ($equipments as $equipment)
                                <option value="{{ $equipment->type_id }}"
                                    {{ old('type_id') == $equipment->type_id ? 'selected' : '' }}>
                                    {{ $equipment->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">ID / SN Number</span>
                        <input type="text" aria-label="ID Num" placeholder="-"
                            class="form-control bg-primary text-light text-center" name="id_num" id="id-num" readonly
                            required value="{{ old('id_num') }}">
                        <input type="text" aria-label="SN Num" placeholder="SN Num"
                            class="form-control w-25 @error('sn_num') is-invalid @enderror {{ old('sn_num') ? 'is-valid' : '' }}"
                            name="sn_num" id="sn-num" required value="{{ old('sn_num') }}">
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-primary text-light">Capacity</span>
                                <input type="text"
                                    class="form-control @error('capacity') is-invalid @enderror {{ old('capacity') ? 'is-valid' : '' }}"
                                    placeholder="Kg / gr / ˚C / mm" id="capacity" name="capacity"
                                    aria-describedby="capacity" required value="{{ old('capacity') }}">
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-primary text-light">Accuracy</span>
                                <span class="input-group-text bg-primary text-light">±</span>
                                <input type="number"
                                    class="form-control @error('accuracy') is-invalid @enderror {{ old('accuracy') ? 'is-valid' : '' }}"
                                    placeholder="± Kg / gr / ˚C / mm" id="accuracy" name="accuracy" step="0.01"
                                    min="0.01" required value="{{ old('accuracy') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="input-group mb-3 h-100 pb-3">
                                <select
                                    class="form-select @error('unit_id') is-invalid @enderror {{ old('unit_id') ? 'is-valid' : '' }}"
                                    id="unit" name="unit_id" required>
                                    <option value="" disabled {{ old('unit_id') ? '' : 'selected' }}>Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->symbol }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Brand</span>
                        <input type="text"
                            class="form-control @error('brand') is-invalid @enderror {{ old('brand') ? 'is-valid' : '' }}"
                            id="brand" name="brand" required value="{{ old('brand') }}">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Calibration Type</span>
                        <select
                            class="form-select @error('calibration_type') is-invalid @enderror {{ old('calibration_type') ? 'is-valid' : '' }}"
                            id="calibration_type" name="calibration_type" required>
                            <option value="" disabled {{ old('calibration_type') ? '' : 'selected' }}>Choose...
                            </option>
                            <option value="Internal" {{ old('calibration_type') == 'Internal' ? 'selected' : '' }}>Internal
                            </option>
                            <option value="External" {{ old('calibration_type') == 'External' ? 'selected' : '' }}>External
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">1st Used</span>
                        <input type="date"
                            class="form-control @error('first_used') is-invalid @enderror {{ old('first_used') ? 'is-valid' : '' }}"
                            id="first_used" name="first_used" required value="{{ old('first_used') }}">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Rank</span>
                        <select class="form-select @error('rank') is-invalid @enderror {{ old('rank') ? 'is-valid' : '' }}"
                            id="rank" name="rank" required>
                            <option value="" disabled {{ old('rank') ? '' : 'selected' }}>AA, A, B, C</option>
                            <option value="AA" {{ old('rank') == 'AA' ? 'selected' : '' }}>AA</option>
                            <option value="A" {{ old('rank') == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('rank') == 'B' ? 'selected' : '' }}>B</option>
                            <option value="C" {{ old('rank') == 'C' ? 'selected' : '' }}>C</option>
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Calibration Freq</span>
                        <input type="number"
                            class="form-control @error('calibration_freq') is-invalid @enderror {{ old('calibration_freq') ? 'is-valid' : '' }}"
                            id="calibration_freq" name="calibration_freq" required value="{{ old('calibration_freq') }}">
                        <span class="input-group-text bg-primary text-light">Month</span>
                    </div>
                    <div class="input-group mb-5">
                        <span class="input-group-text bg-primary text-light">Acceptance Criteria</span>
                        <input type="text"
                            class="form-control @error('acceptance_criteria') is-invalid @enderror {{ old('acceptance_criteria') ? 'is-valid' : '' }}"
                            id="acceptance_criteria" name="acceptance_criteria" required
                            value="{{ old('acceptance_criteria') }}">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">PIC</span>
                        <input type="text"
                            class="form-control @error('pic') is-invalid @enderror {{ old('pic') ? 'is-valid' : '' }}"
                            id="pic" name="pic" required value="{{ old('pic') }}">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-primary text-light">Location</span>
                        <input type="text"
                            class="form-control @error('location') is-invalid @enderror {{ old('location') ? 'is-valid' : '' }}"
                            id="location" name="location" required value="{{ old('location') }}">
                    </div>
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-auto text-center d-flex column-gap-md-5 column-gap-1">
                    <a href="{{ route('dashboard', ['key' => 'menu-input', 'application' => 'KALIBRASI']) }}"
                        class="btn btn-primary">Close</a>
                    <button type="reset" class="btn btn-danger">Reset</button>
                    <button type="submit" class="btn btn-primary">Save & Print</button>
                </div>
            </div>
        </form>
        <x-toast />
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            $('#equipment_name').change(function() {
                const tipeId = $(this).val();

                if (tipeId === "") {
                    $('#id_num').val('');
                    return;
                }

                // Ambil jumlah yang sudah ada untuk tipe ini
                $.get(`/kalibrasi/count-equipments/${tipeId}`, function(data) {
                    const nextNumber = data.count + 1;
                    const paddedNumber = String(nextNumber).padStart(3, '0');
                    const noId = tipeId + '-' + paddedNumber;
                    $('#id-num').val(noId);
                });
            });
        });
    </script>
@endsection
