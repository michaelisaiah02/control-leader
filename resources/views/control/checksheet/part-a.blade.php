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
        <div class="d-flex w-100 mt-2 justify-content-between align-items-center">
            <span class="badge text-bg-primary px-3 py-2">Bagian A (Fixed)</span>
            <span class="border rounded px-3 py-2">Stopwatch: <b id="stopwatch">00:00</b></span>
        </div>

        <form id="formPartA" onsubmit="return false;">
            @csrf
            <div class="row g-3 mt-2">
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
                    {{-- Operator tidak punya akun: input manual --}}
                    <div class="col-md-4">
                        <label class="form-label">ID Operator</label>
                        <input type="text" name="operator_id" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Operator</label>
                        <input type="text" name="operator_name" class="form-control" required>
                    </div>
                @else
                    {{-- Supervisor menilai Leader: pilih dari users role=Leader --}}
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

                <div class="col-md-3">
                    <label class="form-label">Bagian / Divisi</label>
                    <select name="division_id" class="form-select" required>
                        <option value="">- pilih -</option>
                        @foreach ($divisions as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label d-block">Kehadiran</label>
                    <label class="me-3"><input type="radio" name="attendance" value="1" required> Hadir</label>
                    <label><input type="radio" name="attendance" value="0"> Absen</label>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button id="toPartB" class="btn btn-primary">Lanjut ke Bagian B</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Heartbeat anti auto-logout
        const HEARTBEAT_MS = 45000;
        setInterval(() => {
            fetch("{{ route('control.heartbeat') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                keepalive: true
            }).catch(() => {});
        }, HEARTBEAT_MS);

        // Stopwatch (sessionStorage)
        const detailId = {{ $detail->id }};
        const key = (k) => `cl:${detailId}:${k}`;

        if (!sessionStorage.getItem(key('startAt'))) {
            sessionStorage.setItem(key('startAt'), String(Date.now()));
        }

        function renderTimer() {
            const s = Math.max(0, Math.floor((Date.now() - Number(sessionStorage.getItem(key('startAt')))) / 1000));
            document.getElementById('stopwatch').textContent =
                String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
        }
        setInterval(renderTimer, 1000);
        renderTimer();

        // Next → simpan Part A ke sessionStorage, lalu ke Part B
        document.getElementById('toPartB').addEventListener('click', () => {
            const f = document.getElementById('formPartA');
            const fd = new FormData(f);

            const needBase = ['shift', 'division_id', 'attendance'];
            for (const n of needBase)
                if (!fd.get(n)) return alert('Lengkapi semua isian Bagian A.');

            const payload = {
                shift: fd.get('shift'),
                division_id: fd.get('division_id'),
                attendance: fd.get('attendance')
            };

            @if ($planType === 'leader_checks_operator')
                if (!fd.get('operator_id') || !fd.get('operator_name')) return alert('Isi ID & Nama Operator.');
                payload.operator_id = fd.get('operator_id');
                payload.operator_name = fd.get('operator_name');
            @else
                if (!fd.get('person_id')) return alert('Pilih Leader.');
                payload.person_id = fd.get('person_id');
            @endif

            sessionStorage.setItem(key('partA'), JSON.stringify(payload));

            const attendance = encodeURIComponent(payload.attendance);
            window.location.href = "{{ route('control.checksheets.partB', $detail) }}" +
            `?attendance=${attendance}`;
        });
    </script>
@endpush
