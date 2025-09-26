@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border-1 border-white rounded-2 text-uppercase">
        Bagian B
    </p>
@endpush

@section('content')
    <div class="px-5">
        <div class="d-flex w-100 mt-2 justify-content-between align-items-center">
            <p class="border border-2 border-white bg-primary rounded-2 text-white py-2 px-4 shadow">Bagian B</p>
            <p class="border border-2 border-primary rounded-2 px-2 py-2 shadow">Stopwatch:
                <span id="stopwatch" class="py-1 px-2 text-danger bg-danger-subtle"
                    data-start="{{ $startedAtMs }}">00:00</span>
            </p>
        </div>

        <form id="formB" method="POST" action="{{ route('control.checksheets.store') }}">
            @csrf
            <input type="hidden" name="phase" value="{{ $phase }}">

            @foreach ($questions as $i => $q)
                <div class="q-block mb-4 p-3 border border-2 rounded-4" id="q_{{ $i + 1 }}"
                    data-idx="{{ $i + 1 }}">
                    <div class="fw-semibold mb-2">{{ $i + 1 }}. {!! nl2br(e($q->question_text)) !!}</div>

                    @php
                        // 1) Ambil choices dari DB/casts atau pakai default
                        $raw =
                            $q->choices ?:
                            ($q->answer_type === 'b'
                                ? [['value' => '0', 'label' => 'Tidak'], ['value' => '1', 'label' => 'Ya']]
                                : [
                                    ['value' => '0', 'label' => 'Pilihan 0'],
                                    ['value' => '1', 'label' => 'Pilihan 1'],
                                    ['value' => '2', 'label' => 'Pilihan 2'],
                                ]);

                        // 2) Normalisasi ke map "val => label" apapun bentuk inputnya
                        //    - Support: ["0"=>"Tidak", "1"=>"Ya"]
                        //    - Support: [ ["value"=>"0","label"=>"Tidak"], ["value"=>"1","label"=>"Ya"] ]
                        //    - Support: [ ["val"=>"0","text"=>"Tidak"] ] (fallback kunci umum)
                        $choices = collect($raw)
                            ->mapWithKeys(function ($item, $k) {
                                if (is_array($item)) {
                                    // ambil value dan label dari berbagai kemungkinan key
                                    $val = (string) ($item['value'] ?? ($item['val'] ?? ($item['key'] ?? $k)));
                                    $label = (string) ($item['label'] ?? ($item['text'] ?? ($item['name'] ?? $val)));
                                    return [$val => $label];
                                } else {
                                    // kalau array map sederhana "key => value"
                                    return [(string) $k => (string) $item];
                                }
                            })
                            ->all();
                    @endphp


                    <div class="ms-3">
                        @foreach ($choices as $val => $label)
                            {{-- @dd($label) --}}
                            <div class="form-check">
                                <input class="form-check-input answer-radio" type="radio"
                                    name="answers[{{ $q->id }}]" id="q{{ $q->id }}_{{ $val }}"
                                    value="{{ $val }}" data-type="{{ $q->answer_type }}"
                                    data-qid="{{ $q->id }}">
                                <label class="form-check-label"
                                    for="q{{ $q->id }}_{{ $val }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>

                    {{-- Problem & Countermeasure (kondisional) --}}
                    <div class="mt-3 ps-2 d-none pb-prob" id="probwrap-{{ $q->id }}">
                        <div class="fw-semibold mb-1">{{ $q->problem_label ?? 'Problem' }}</div>
                        <input type="text" class="form-control bg-warning-subtle" name="problems[{{ $q->id }}]"
                            placeholder="Reason">
                    </div>
                    <div class="mt-3 ps-2 d-none pb-cm" id="cmwrap-{{ $q->id }}">
                        <div class="fw-semibold mb-1">{{ $q->countermeasure_label ?? 'Countermeasure' }}</div>
                        <input type="text" class="form-control bg-warning-subtle"
                            name="countermeasures[{{ $q->id }}]" placeholder="Reason dan Countermeasure">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-secondary" id="btnPrev">Back</button>
                    <div>Halaman <span id="pageNow">1</span>/<span id="pageTotal"></span></div>
                    <button type="button" class="btn btn-primary" id="btnNext">Next</button>
                    <button type="submit" class="btn btn-primary d-none" id="btnSubmit">Submit</button>
                </div>
            @endforeach

            <div class="d-flex justify-content-end gap-3 mt-3">
                <a class="btn btn-secondary" href="{{ route('control.checksheets.create', ['type' => $phase]) }}">Back</a>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(function() {
            // Stopwatch lanjut
            const startedAt = parseInt($('#stopwatch').data('start'), 10) || Date.now();
            setInterval(function() {
                const sec = Math.floor((Date.now() - startedAt) / 1000);
                const mm = String(Math.floor(sec / 60)).padStart(2, '0');
                const ss = String(sec % 60).padStart(2, '0');
                $('#stopwatch').text(`${mm}:${ss}`);
            }, 1000);

            // Heartbeat
            setInterval(() => $.post("{{ route('control.heartbeat') }}", {
                _token: '{{ csrf_token() }}'
            }), 45000);

            // Logic problem/countermeasure:
            // - type a (0/1/2): show prob+cm jika 0 atau 1
            // - type b (0/1):   show prob+cm jika 0
            // - type c:         (contoh) tanpa kewajiban; kalau mau wajib, ubah di sini
            $('.answer-radio').on('change', function() {
                const qid = $(this).data('qid');
                const type = $(this).data('type');
                const val = String($(this).val());

                const showPC = (type === 'a' && (val === '0' || val === '1')) ||
                    (type === 'b' && val === '0');

                $('#probwrap-' + qid).toggleClass('d-none', !showPC);
                $('#cmwrap-' + qid).toggleClass('d-none', !showPC);
            });
        });
    </script>
    <script type="module">
        $(function() {
            // ===== config
            const PAGE_SIZE = 5;
            const detailId = {{ $detail->id }};
            const phase = @json($phase);
            const keyRoot = (k) => `cl:${detailId}:${phase}:${k}`; // sessionStorage key

            // ===== stopwatch
            let startedAt = parseInt($("#stopwatch").data("start"));

            function tick() {
                const s = Math.floor((Date.now() - startedAt) / 1000);
                $("#stopwatch").text(
                    String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0')
                );
            }
            setInterval(tick, 1000);
            tick();

            // ===== heartbeat
            setInterval(() => $.post(@json(route('control.heartbeat')), {
                _token: @json(csrf_token())
            }), 45000);

            // ===== paging
            const $blocks = $('.q-block');
            const TOTAL = $blocks.length;
            const PAGES = Math.max(1, Math.ceil(TOTAL / PAGE_SIZE));
            let page = Number(sessionStorage.getItem(keyRoot('page')) || 1);
            page = Math.min(Math.max(1, page), PAGES);

            $('#pageTotal').text(PAGES);

            function showPage(p) {
                page = p;
                sessionStorage.setItem(keyRoot('page'), page);

                $blocks.addClass('d-none');
                const start = (page - 1) * PAGE_SIZE + 1;
                const end = Math.min(page * PAGE_SIZE, TOTAL);

                for (let i = start; i <= end; i++) {
                    $(`.q-block[data-idx="${i}"]`).removeClass('d-none');
                }

                $('#pageNow').text(page);
                $('#btnPrev').prop('disabled', page === 1);
                $('#btnNext').toggleClass('d-none', page === PAGES);
                $('#btnSubmit').toggleClass('d-none', page !== PAGES);
            }

            $('#btnPrev').on('click', () => showPage(Math.max(1, page - 1)));
            $('#btnNext').on('click', () => showPage(Math.min(PAGES, page + 1)));

            // ===== persist jawaban
            const loadAll = () => JSON.parse(sessionStorage.getItem(keyRoot('b_answers')) || '{}');
            const saveAll = (o) => sessionStorage.setItem(keyRoot('b_answers'), JSON.stringify(o || {}));

            // restore semua radio & input
            const saved = loadAll();
            for (const qid in saved) {
                const a = saved[qid] || {};
                if (a.value !== undefined) {
                    $(`input[name="answers[${qid}][value]"][value="${a.value}"]`).prop('checked', true);
                }
                if (a.problem) $(`input[name="answers[${qid}][problem]"]`).val(a.problem);
                if (a.countermeasure) $(`input[name="answers[${qid}][countermeasure]"]`).val(a.countermeasure);
                togglePCM(qid, a.value);
            }

            function togglePCM(qid, value) {
                const $blk = $(`.q-block [name="answers[${qid}][value]"]`).closest('.q-block');
                const requireArr = ($blk.data('require') || []);
                const show = requireArr.includes(String(value));
                $blk.find('.need-pcm').toggleClass('d-none', !show);
            }

            $('.answer-radio').on('change', function() {
                const qid = $(this).data('qid');
                const value = $(this).val();
                const label = $(this).closest('.form-check').find('label').text().trim();

                const all = loadAll();
                all[qid] = {
                    ...(all[qid] || {}),
                    value,
                    label
                };
                saveAll(all);
                togglePCM(qid, value);

                // isi hidden label snapshot bila ada
                $(`.q-block[data-qid="${qid}"] .answer-label`).val(label);
            });

            $('.need-pcm input').on('input', function() {
                const m = $(this).attr('name').match(/answers\[(\d+)\]\[(.+)\]/);
                if (!m) return;
                const qid = m[1],
                    field = m[2];
                const all = loadAll();
                all[qid] = {
                    ...(all[qid] || {}),
                    [field]: $(this).val()
                };
                saveAll(all);
            });

            // sebelum submit: inject semua dari sessionStorage (biar aman)
            $('#partBForm').on('submit', function() {
                const all = loadAll();
                const $persist = $('#persistInputs').empty();
                Object.keys(all).forEach(qid => {
                    const a = all[qid] || {};
                    if (a.value !== undefined)
                        $persist.append(
                            `<input type="hidden" name="answers[${qid}][value]" value="${a.value}">`
                            );
                    if (a.label !== undefined)
                        $persist.append(
                            `<input type="hidden" name="answers[${qid}][label]" value="${$('<div>').text(a.label).html()}">`
                            );
                    if (a.problem !== undefined)
                        $persist.append(
                            `<input type="hidden" name="answers[${qid}][problem]" value="${$('<div>').text(a.problem).html()}">`
                            );
                    if (a.countermeasure !== undefined)
                        $persist.append(
                            `<input type="hidden" name="answers[${qid}][countermeasure]" value="${$('<div>').text(a.countermeasure).html()}">`
                            );
                });
                sessionStorage.removeItem(keyRoot('b_answers'));
            });

            // first render
            showPage(page);
        });
    </script>
@endsection
