@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
        @switch($phase)
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
                JUDUL CHECKSHEET
        @endswitch
    </p>
@endpush

@section('content')
    <div class="px-5">
        <div class="d-flex w-100 mt-2 justify-content-between align-items-center">
            <p class="border border-2 border-white bg-primary rounded-2 text-white py-1 mb-1 px-4 shadow">Bagian B</p>
            <p class="border border-2 border-primary rounded-2 px-2 py-1 mb-1 shadow">Stopwatch:
                <span id="stopwatch" class="mb-1 px-2 text-danger bg-danger-subtle">00:00</span>
            </p>
        </div>

        <form id="formB" method="POST" action="{{ route('control.checksheets.store', ['type' => $phase]) }}">
            @csrf
            <input type="hidden" name="schedule_plan_id" value="{{ $plan->id }}">
            <input type="hidden" name="part_a[shift]">
            <input type="hidden" name="part_a[target]">
            <input type="hidden" name="part_a[division]">
            <input type="hidden" name="part_a[attendance]">
            <input type="hidden" name="part_a[condition]">
            <input type="hidden" name="part_a[replacement_name]">
            <input type="hidden" name="part_a[replacement_division]">
            <input type="hidden" name="part_a[replacement_condition]">

            @foreach ($questions as $i => $q)
                @php $idx = $i+1; @endphp
                <div id="q_{{ $idx }}" class="card mb-3 question-card {{ $idx > 1 ? 'd-none' : '' }}">
                    <div class="card-body">
                        <div class="fw-semibold mb-2">{{ $idx }}. {{ $q->question_text }}</div>
                        @php
                            $choices =
                                $q->choices ?:
                                ($q->answer_type === 'b'
                                    ? ['0' => 'Tidak', '1' => 'Ya']
                                    : ['0' => 'Pilihan 0', '1' => 'Pilihan 1', '2' => 'Pilihan 2']);
                        @endphp
                        <div class="ms-3">
                            @foreach ($choices as $val => $label)
                                @php
                                    $v = (string) $val;
                                    $lbl = is_array($label) ? $label['label'] ?? json_encode($label) : $label;
                                @endphp
                                <div class="form-check">
                                    <input class="form-check-input answer-radio" type="radio"
                                        name="answers[{{ $q->id }}]" id="q{{ $q->id }}_{{ $v }}"
                                        value="{{ $v }}" data-type="{{ $q->answer_type }}"
                                        data-qid="{{ $q->id }}">
                                    <label class="form-check-label"
                                        for="q{{ $q->id }}_{{ $v }}">{{ $lbl }}</label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Problem/Countermeasure slot (muncul tergantung jawaban) --}}
                        <div class="mt-3 problem-wrap d-none">
                            <label class="form-label">{{ $q->problem_label ?? 'Problem' }}</label>
                            <input type="text" name="problems[{{ $q->id }}]"
                                class="form-control bg-warning-subtle" placeholder="Reason">
                        </div>
                        <div class="mt-2 counter-wrap d-none">
                            <label class="form-label">{{ $q->countermeasure_label ?? 'Countermeasure' }}</label>
                            <input type="text" name="countermeasures[{{ $q->id }}]"
                                class="form-control bg-warning-subtle" placeholder="Reason dan Countermeasure">
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-between">
                <span id="pageInfo" class="me-3">{{ count($questions) ? '1 / ' . count($questions) : '' }}</span>
                <div>
                    <button type="button" id="prevQ" class="btn btn-outline-secondary">Back</button>
                    <button type="button" id="nextQ" class="btn btn-primary">Next</button>
                    <button type="submit" id="submitBtn" class="btn btn-success d-none">Submit</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(function() {
            const PHASE = @json($phase);
            const PLAN = {{ $plan->id }};
            const key = (k) => `cl:plan:${PLAN}:phase:${PHASE}:${k}`;
            // Stopwatch: pakai started_at_ms yang disimpan di Part A (server draft)
            // Ambil dari sessionStorage backup kalau ada
            let started = Number(sessionStorage.getItem(key('started_ms')) || 0);
            if (!started) {
                started = Date.now();
            } // fallback
            setInterval(() => {
                const sec = Math.floor((Date.now() - started) / 1000);
                const mm = String(Math.floor(sec / 60)).padStart(2, '0');
                const ss = String(sec % 60).padStart(2, '0');
                $('#stopwatch').text(`${mm}:${ss}`);
            }, 1000);

            // inject Part A hidden dari sessionStorage
            try {
                const a = JSON.parse(sessionStorage.getItem(key('partA')) || 'null');
                console.log('a=', a);
                if (a) {
                    $('[name="part_a[shift]"]').val(a.shift);
                    $('[name="part_a[target]"]').val(a.target);
                    $('[name="part_a[division]"]').val(a.bagian);
                    $('[name="part_a[attendance]"]').val(a.attendance);
                    $('[name="part_a[condition]"]').val(a.kondisi);
                    $('[name="part_a[replacement_name]"]').val(a.nama_pengganti);
                    $('[name="part_a[replacement_division]"]').val(a.bagian_pengganti);
                    $('[name="part_a[replacement_condition]"]').val(a.kondisi_pengganti);
                    sessionStorage.setItem(key('started_ms'), String(started)); // keep
                }
            } catch (e) {}

            // simple pager
            const TOTAL = $('.question-card').length;
            let cur = 1;
            const go = (n) => {
                if (n < 1 || n > TOTAL) return;
                $(`#q_${cur}`).addClass('d-none');
                cur = n;
                $(`#q_${cur}`).removeClass('d-none');
                $('#prevQ').prop('hidden', cur === 1);
                $('#nextQ').toggleClass('d-none', cur === TOTAL);
                $('#submitBtn').toggleClass('d-none', cur !== TOTAL);
                $('#pageInfo').text(`${cur} / ${TOTAL}`);
            };

            const showToast = (message, type = 'warning') => {
                // Buat toast secara programatik dengan jQuery
                const toastEl = $('<div>').addClass('toast align-items-center border-0')
                    .addClass(`text-bg-${type}`)
                    .attr('role', 'alert')
                    .attr('aria-live', 'assertive')
                    .attr('aria-atomic', 'true');

                const flexDiv = $('<div>').addClass('d-flex');
                const bodyDiv = $('<div>').addClass('toast-body').text(message);
                const closeBtn = $('<button>').attr('type', 'button')
                    .addClass('btn-close btn-close-white me-2 m-auto')
                    .attr('data-bs-dismiss', 'toast')
                    .attr('aria-label', 'Close');

                flexDiv.append(bodyDiv).append(closeBtn);
                toastEl.append(flexDiv);

                let toastContainer = $('.toast-container');
                if (toastContainer.length === 0) {
                    toastContainer = $('<div>').addClass(
                        'toast-container position-fixed top-50 end-0 translate-middle-y p-3');
                    $('body').append(toastContainer);
                }

                toastContainer.append(toastEl);

                const toast = new bootstrap.Toast(toastEl[0]);
                toast.show();

                toastEl.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            };

            const validateCurrentQuestion = () => {
                const currentCard = $(`#q_${cur}`);
                const radios = currentCard.find('.answer-radio');
                const isAnswered = radios.is(':checked');
                const problemInput = currentCard.find('.problem-wrap input');
                const counterInput = currentCard.find('.counter-wrap input');
                const isProblemVisible = currentCard.find('.problem-wrap').is(':visible');
                const isCounterVisible = currentCard.find('.counter-wrap').is(':visible');

                if (!isAnswered) {
                    showToast('Silakan pilih jawaban untuk pertanyaan ini sebelum melanjutkan.');
                    return false;
                }
                if (isProblemVisible && problemInput.val().trim() === '') {
                    showToast('Silakan isi kolom Problem sebelum melanjutkan.');
                    return false;
                }
                if (isCounterVisible && counterInput.val().trim() === '') {
                    showToast('Silakan isi kolom Countermeasure sebelum melanjutkan.');
                    return false;
                }
                return true;
            };

            $('#prevQ').on('click', () => go(cur - 1));
            $('#nextQ').on('click', () => {
                if (validateCurrentQuestion()) {
                    go(cur + 1);
                }
            });
            go(1);

            // tampilkan problem/countermeasure sesuai rule:
            // - type A (3 pilihan): jika value 0 atau 1 → tampilkan keduanya
            // - type B (2 pilihan): jika value 0 → tampilkan keduanya
            // - type C (3 pilihan, tanpa prob/cm) → tidak ada (biarin)
            $('.answer-radio').on('change', function() {
                const type = $(this).data('type'); // a|b|c
                const qid = $(this).data('qid');
                const val = $(this).val();
                const card = $(this).closest('.card-body');
                const show = (type === 'a' && (val === '0' || val === '1')) || (type === 'b' && val ===
                    '0');
                card.find('.problem-wrap').toggleClass('d-none', !show);
                card.find('.counter-wrap').toggleClass('d-none', !show);
            });

            // submit: validate before submitting
            $('#formB').on('submit', function(e) {
                if (!validateCurrentQuestion()) {
                    e.preventDefault();
                    return false;
                }
                // hapus session draft
                sessionStorage.removeItem(key('partA'));
                sessionStorage.removeItem(key('started_ms'));
            });
        });
    </script>
@endsection
