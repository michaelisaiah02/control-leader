@extends('layouts.app')

@section('content')
    <div class="container mt-md-3">
        <form action="{{ route('kalibrasi.report.search') }}" method="GET">
            <div class="row justify-content-md-around justify-content-center my-3">
                <div class="col-md">
                    <div class="input-group mb-md-5 mb-1">
                        <span class="input-group-text bg-primary text-light">Date</span>
                        <input type="date" aria-label="Date" placeholder="Search" class="form-control text-center"
                            name="date_from" id="date-from" max="{{ now()->toDateString() }}">
                        <span class="input-group-text bg-primary text-light">to</span>
                        <input type="date" aria-label="Date" placeholder="Search" class="form-control text-center"
                            name="date_to" id="date-to" max="{{ now()->toDateString() }}">
                        <div class="invalid-feedback">The "From" date must be earlier than the "To" date.</div>
                    </div>
                    <div class="input-group mb-md-5 mb-1">
                        <label class="input-group-text bg-primary text-light" for="Name-alat-ukur">Location</label>
                        <input type="text" aria-label="Location" placeholder="Search" class="form-control w-25"
                            name="location" id="location">
                    </div>
                    <div class="input-group mb-md-5 mb-1">
                        @if (auth()->user()->approved == true)
                            <span class="input-group-text bg-primary text-light">Approved</span>
                            <select class="form-select" id="is_approved" name="is_approved">
                                <option value="" selected>All</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        @endif
                        @if (auth()->user()->checked == true)
                            <span class="input-group-text bg-primary text-light">Checked</span>
                            <select class="form-select" id="is_checked" name="is_checked">
                                <option value="" selected>All</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        @endif
                    </div>
                </div>
                <div class="col-md">
                    <div class="input-group mb-md-5 mb-1">
                        <span class="input-group-text bg-primary text-light">Calibration Type</span>
                        <select class="form-select" id="calibration_type" name="calibration_type">
                            <option value="" selected>Choose...</option>
                            <option value="Internal">Internal</option>
                            <option value="External">External</option>
                        </select>
                    </div>
                    <div class="input-group mb-md-5 mb-1">
                        <span class="input-group-text bg-primary text-light">Equipment Name</span>
                        <select class="form-select" id="equipment_name" name="type_id">
                            <option value="" selected>Choose...</option>
                            @foreach ($equipments as $equipment)
                                <option value="{{ $equipment->type_id }}">{{ $equipment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group mb-md-5 mb-1">
                        <span class="input-group-text bg-primary text-light">Judgement</span>
                        <select class="form-select" id="judgement" name="judgement">
                            <option value="" selected>OK / NG / Disposal</option>
                            <option value="OK">OK</option>
                            <option value="NG">NG</option>
                            <option value="Disposal">Disposal</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="input-group mb-md-5 mb-1">
                <button type="submit" class="btn btn-secondary me-0 me-md-4 rounded-1" value="masterlist"
                    name="master_lists">Masterlist</button>
                <button type="submit" class="btn btn-secondary mx-md-4 rounded-1" value="history"
                    name="repairs">Repair</button>
            </div>
        </form>
        <div id="btn-back" class="row justify-content-end">
            <div class="col-auto">
                <a href="{{ route('dashboard', ['key' => 'menu-kalibrasi', 'application' => 'KALIBRASI']) }}"
                    class="btn btn-primary">Close</a>
            </div>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            $('[name="type_id"]').selectize({
                theme: 'bootstrap5'
            })[0].selectize;
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFrom = document.getElementById('date-from');
            const dateTo = document.getElementById('date-to');
            const now = new Date().toISOString().split('T')[0];

            dateFrom.addEventListener('change', function() {
                if (dateFrom.value && dateTo.value && dateFrom.value > dateTo.value) {
                    const invalidFeedback = document.querySelector('.invalid-feedback');
                    invalidFeedback.style.display = 'block';
                    setTimeout(() => {
                        invalidFeedback.style.display = 'none';
                    }, 3000);
                    dateTo.value = '';
                }
            });

            dateTo.addEventListener('change', function() {
                if (dateFrom.value && dateTo.value && dateFrom.value > dateTo.value) {
                    const invalidFeedback = document.querySelector('.invalid-feedback');
                    invalidFeedback.style.display = 'block';
                    setTimeout(() => {
                        invalidFeedback.style.display = 'none';
                    }, 3000);
                    dateTo.value = '';
                }
            });
        });
    </script>
@endsection
