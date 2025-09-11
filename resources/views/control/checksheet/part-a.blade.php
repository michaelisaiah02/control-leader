@extends('layouts.app')

@push('subtitle')
    <p id="title" class="fs-2 w-75 p-0 my-auto sub-judul border-1 border-white rounded-2 text-uppercase">
        @switch($slot)
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
    <div class="px-4">
        <div class="d-flex justify-content-between my-2">
            <span class="badge text-bg-primary">Bagian A (Fixed)</span>
            <span class="border rounded px-3 py-2">Stopwatch: <b id="stopwatch">00:00</b></span>
        </div>

        <div class="mb-2 text-muted">
            Departemen (dari scheduler): <b>{{ $schedulerDeptName }}</b>
        </div>

        <form id="formPartA" onsubmit="return false;">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Shift</label>
                    <select name="shift" class="form-select" required>
                        <option value="">- pilih -</option>
                        <option value="1">Shift 1</option>
                        <option value="2">Shift 2</option>
                        <option value="3">Shift 3</option>
                    </select>
                </div>

                @if ($planType === 'leader_checks_operator')
                    <div class="col-md-4">
                        <label class="form-label">ID Operator</label>
                        <input type="text" name="operator_id" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Operator</label>
                        <input type="text" name="operator_name" class="form-control" required>
                    </div>
                @else
                    <div class="col-md-9">
                        <label class="form-label">Leader (ID & Nama)</label>
                        <select name="person_id" class="form-select" required>
                            <option value="">- pilih -</option>
                            @foreach ($leaders as $p)
                                <option value="{{ $p->id }}">{{ $p->employeeID ?? 'LDR' . $p->id }} —
                                    {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12">
                    <label class="form-label d-block">Kehadiran</label>
                    <label class="me-3"><input type="radio" name="attendance" value="1" required> Hadir</label>
                    <label><input type="radio" name="attendance" value="0"> Absen</label>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <button id="toPartB" class="btn btn-primary">Lanjut ke Bagian B</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // heartbeat + stopwatch tetap
        const detailId = {{ $detail->id }};
        const key = k => `cl:${detailId}:${k}`;

        if (!sessionStorage.getItem(key('startAt'))) sessionStorage.setItem(key('startAt'), String(Date.now()));

        setInterval(() => {
            const s = Math.floor((Date.now() - Number(sessionStorage.getItem(key('startAt')))) / 1000);
            document.getElementById('stopwatch').textContent =
                String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
        }, 1000);

        setInterval(() => {
            fetch("{{ route('control.heartbeat') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                keepalive: true
            });
        }, 45000);

        document.getElementById('toPartB').addEventListener('click', () => {
            const f = document.getElementById('formPartA');
            const fd = new FormData(f);
            const need = ['shift', 'attendance'];
            for (const n of need)
                if (!fd.get(n)) return alert('Lengkapi form.');

            const payload = {
                shift: fd.get('shift'),
                attendance: fd.get('attendance')
            };
            @if ($planType === 'leader_checks_operator')
                if (!fd.get('operator_id') || !fd.get('operator_name')) return alert('Isi ID & Nama operator.');
                payload.operator_id = fd.get('operator_id');
                payload.operator_name = fd.get('operator_name');
            @else
                if (!fd.get('person_id')) return alert('Pilih leader.');
                payload.person_id = fd.get('person_id');
            @endif
            sessionStorage.setItem(key('partA'), JSON.stringify(payload));

            const q = new URLSearchParams({
                attendance: payload.attendance
            });
            window.location.href = "{{ route('control.checksheets.partB', $detail) }}?" + q.toString();
        });
    </script>
@endpush
