@extends('layouts.app')

@push('subtitle')
    <p class="fs-5 m-0">CHECKSHEET — Bagian B</p>
@endpush
@section('content')
    <div class="px-4">
        <div class="d-flex justify-content-between align-items-center my-3">
            <span class="badge text-bg-secondary px-3 py-2">Bagian B (Dinamis)</span>
            <span class="border rounded px-3 py-2">Stopwatch:
                <b id="stopwatch">00:00</b>
            </span>
        </div>

        <form id="formAll" method="POST" action="{{ route('control.checksheets.store') }}">
            @csrf

            {{-- Hidden: Part A + meta --}}
            <input type="hidden" name="schedule_detail_id" value="{{ $detail->id }}">
            <input type="hidden" name="shift">
            <input type="hidden" name="person_id">
            <input type="hidden" name="division_id">
            <input type="hidden" name="attendance">
            <input type="hidden" name="stopwatch_duration" id="stopwatch_duration">

            {{-- List pertanyaan --}}
            @forelse($questions as $i => $q)
                <div class="card mb-3">
                    <div class="card-header">
                        #{{ $q->display_order }} — {{ $q->code ?? 'Q' . $q->id }}
                    </div>
                    <div class="card-body">
                        <p class="mb-2">{{ $q->prompt }}</p>

                        <input type="hidden" name="answers[{{ $i }}][question_id]" value="{{ $q->id }}">

                        <div class="mb-2">
                            <label class="form-label">Jawaban</label>
                            <input type="text" class="form-control" name="answers[{{ $i }}][answer]"
                                placeholder="isikan jawaban singkat…">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Problem (opsional)</label>
                                <textarea class="form-control" name="answers[{{ $i }}][problem]" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Countermeasure (opsional)</label>
                                <textarea class="form-control" name="answers[{{ $i }}][countermeasure]" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">Tidak ada pertanyaan untuk kombinasi ini.</div>
            @endforelse

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('control.checksheets.create', $detail) }}" class="btn btn-outline-secondary">
                    &larr; Kembali ke Bagian A
                </a>
                <button type="submit" class="btn btn-primary">Simpan Checksheet</button>
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

        // === Restore Part A + stopwatch, submit sekali ke server ===
        const detailId = {{ $detail->id }};
        const key = (k) => `cl:${detailId}:${k}`;

        // Stopwatch display
        function renderTimer() {
            const startAt = Number(sessionStorage.getItem(key('startAt')) || Date.now());
            const s = Math.max(0, Math.floor((Date.now() - startAt) / 1000));
            const m = String(Math.floor(s / 60)).padStart(2, '0');
            const sec = String(s % 60).padStart(2, '0');
            document.getElementById('stopwatch').textContent = `${m}:${sec}`;
        }
        setInterval(renderTimer, 1000);
        renderTimer();

        // Inject hidden fields dari Part A saat submit
        const form = document.getElementById('formAll');
        form.addEventListener('submit', () => {
            const partA = JSON.parse(sessionStorage.getItem(key('partA') || '{}') || '{}');

            form.elements['shift'].value = partA.shift ?? '';
            form.elements['division_id'].value = partA.division_id ?? '';
            form.elements['attendance'].value = partA.attendance ?? '';

            @if ($detail->plan->type === 'leader_checks_operator')
                // operator manual → kirim operator_id/operator_name, BUKAN person_id
                const opId = document.createElement('input');
                opId.type = 'hidden';
                opId.name = 'operator_id';
                opId.value = partA.operator_id ?? '';
                form.appendChild(opId);

                const opNm = document.createElement('input');
                opNm.type = 'hidden';
                opNm.name = 'operator_name';
                opNm.value = partA.operator_name ?? '';
                form.appendChild(opNm);
            @else
                // supervisor_checks_leader → kirim person_id (leader id)
                form.elements['person_id'].value = partA.person_id ?? '';
            @endif

            const startAt = Number(sessionStorage.getItem(key('startAt')) || Date.now());
            const elapsed = Math.max(0, Math.floor((Date.now() - startAt) / 1000));
            document.getElementById('stopwatch_duration').value = String(elapsed);

            // bersihkan storage agar gak nempel
            sessionStorage.removeItem(key('partA'));
            sessionStorage.removeItem(key('startAt'));
        });

        // Optional: kalau user masuk ke Part B tanpa Part A, balikin
        (function guardPartA() {
            if (!sessionStorage.getItem(key('partA'))) {
                window.location.href = "{{ route('control.checksheets.create', $detail) }}";
            }
        })();
    </script>
@endpush
