@extends('layouts.app')

@push('subtitle')
    <p id="title" class="fs-2 w-75 p-0 my-auto sub-judul border-1 border-white rounded-2 text-uppercase">
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
    <div class="px-4">
        <div class="d-flex justify-content-between align-items-center my-3">
            <span class="badge text-bg-primary px-3 py-2">Bagian A (Fixed)</span>
            <span class="border rounded px-3 py-2">Stopwatch:
                <b id="stopwatch">00:00</b>
            </span>
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

                <div class="col-md-5">
                    <label class="form-label">
                        @if ($planType === 'leader_checks_operator')
                            Operator
                        @else
                            Leader
                        @endif (ID & Nama)
                    </label>
                    <select name="person_id" class="form-select" required>
                        <option value="">- pilih -</option>
                        @foreach ($people as $p)
                            <option value="{{ $p->id }}">{{ $p->id }} — {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
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
                    <label class="me-3">
                        <input type="radio" name="attendance" value="1" required> Hadir
                    </label>
                    <label>
                        <input type="radio" name="attendance" value="0"> Absen
                    </label>
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
        // === Heartbeat anti auto-logout ===
        const HEARTBEAT_MS = 45000;
        const hb = setInterval(() => {
            fetch("{{ route('control.heartbeat') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                keepalive: true
            }).catch(() => {});
        }, HEARTBEAT_MS);

        // === Stopwatch nyala dari Part A, disimpan di sessionStorage ===
        const detailId = {{ $detail->id }};
        const key = (k) => `cl:${detailId}:${k}`;

        if (!sessionStorage.getItem(key('startAt'))) {
            sessionStorage.setItem(key('startAt'), String(Date.now()));
        }

        function renderTimer() {
            const startAt = Number(sessionStorage.getItem(key('startAt')));
            const s = Math.max(0, Math.floor((Date.now() - startAt) / 1000));
            const m = String(Math.floor(s / 60)).padStart(2, '0');
            const sec = String(s % 60).padStart(2, '0');
            document.getElementById('stopwatch').textContent = `${m}:${sec}`;
        }
        setInterval(renderTimer, 1000);
        renderTimer();

        // === Simpan Part A → pindah ke Part B
        document.getElementById('toPartB').addEventListener('click', () => {
            const form = document.getElementById('formPartA');
            const fd = new FormData(form);

            const needed = ['shift', 'person_id', 'division_id', 'attendance'];
            for (const n of needed) {
                if (!fd.get(n)) {
                    alert('Lengkapi semua isian Bagian A.');
                    return;
                }
            }

            const data = {};
            needed.forEach(n => data[n] = fd.get(n));
            sessionStorage.setItem(key('partA'), JSON.stringify(data));

            // Kirim attendance via querystring (opsional) biar Part B bisa filter pertanyaan
            const attendance = encodeURIComponent(data.attendance);
            window.location.href = "{{ route('control.checksheets.partB', $detail) }}" +
            `?attendance=${attendance}`;
        });
    </script>
@endpush
