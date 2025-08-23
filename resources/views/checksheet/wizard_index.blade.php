<!-- Dynamic: slide 7 dan 8 di PPT -->

@extends('layouts.app')

@push('subtitle')
    <p id="title" class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        @switch($type)
            @case('awal_shift')
                AWAL SHIFT SEBELUM BEKERJA
            @break

            @case('saat_bekerja')
                SAAT BEKERJA
            @break

            @case('setelah_istirahat')
                SETELAH ISTIRAHAT
            @break

            @case('akhir_shift')
                AKHIR SHIFT SEBELUM PULANG
            @break

            @default
        @endswitch
    </p>
@endpush

@section('content')
    <div class="px-5">
        <div class="d-flex w-100 my-2 justify-content-between align-items-center">
            <p class="border border-2 border-white bg-primary rounded-2 text-white py-1 px-4 shadow">Bagian A</p>
            <p class="border border-2 border-primary rounded-2 px-2 py-1 shadow">Stopwatch: <span
                    class="py-1 px-2 text-danger bg-danger-subtle">00:00</span></p>
        </div>
        <div class="d-flex flex-column w-100 gap-3">
            <div class="p-2 w-100 border border-2 border-primary rounded-2">
                <p> 1. Check kehadiran operator <br /> Apabila operator tidak masuk: </p>
                <ul>
                    <li>Isi perubahan Man Power di Henkaten Board</li>
                    <li>Operator pengganti harus sesuai Skill Map</li>
                    <li>Konfirmasi hasil awal untuk operator pengganti (Hasil awal kerja OK)</li>
                </ul>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="status-absent" value="absent">
                        <label class="form-check-label" for="status-absent">0) Absen</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="status-present" value="present">
                        <label class="form-check-label" for="status-present">1) Hadir</label>
                    </div>
                </div>

            </div>

            <form action="" id="dynamic-forms">
                @foreach ($steps as $step)
                    <x-forms.wizard :id="$step['id']" :name="$step['name']" :type="$step['type']" :placeholder="$step['placeholder'] ?? ''"
                        :label="$step['label']" :options="$step['options'] ?? []" />
                @endforeach
            </form>
        </div>

        <div class="w-100 d-flex justify-content-end mt-3">
            <div class="d-flex gap-2">
                <a href="#" class="btn btn-primary rounded-2 px-5 border border-2 border-white shadow">Back</a>
                <a href="#" class="btn btn-primary rounded-2 px-5 border border-2 border-white shadow">Next</a>
            </div>
        </div>
    </div>

    <script>
        const steps = @json($steps);
        let currentMode = "";
        let filteredSteps = [];

        function filterSteps(mode) {
            return steps.filter(step => step.id.includes(mode));
        }

        function initForm(mode) {
            currentMode = mode;
            filteredSteps = filterSteps(mode);

            steps.forEach(step => {
                const el = document.getElementById(step.id);
                if (el) el.classList.add("d-none");
            });

            if (filteredSteps.length > 0) {
                document.getElementById(filteredSteps[0].id).classList.remove("d-none");
            }
        }

        function showNextStep(index) {
            if (index + 1 < filteredSteps.length) {
                document.getElementById(filteredSteps[index + 1].id).classList.remove("d-none");
            }
        }

        function attachListeners() {
            steps.forEach((step, idx) => {
                const el = document.getElementById(step.id);
                if (!el) return;

                const inputs = el.querySelectorAll("input");
                inputs.forEach(input => {
                    input.addEventListener("change", () => {
                        const filteredIndex = filteredSteps.findIndex(s => s.id === step.id);
                        if (filteredIndex !== -1) {
                            showNextStep(filteredIndex);
                        }
                    });
                });
            });
        }

        document.querySelectorAll('input[name="status"]').forEach(radio => {
            radio.addEventListener("change", function() {
                initForm(this.value);
            });
        });

        initForm("");
        attachListeners();
    </script>
@endsection
