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
        @endswitch
    </p>
@endpush

@section('content')
    <div class="px-5">
        <div class="d-flex w-100 mt-2 justify-content-between align-items-center">
            <p class="border-2 border-white bg-primary rounded-2 text-white py-2 px-4 shadow">Bagian A</p>
            <p class="border-2 border-primary rounded-2 px-2 py-2 shadow">Stopwatch:
                <span id="stopwatch" class="py-1 px-2 text-danger bg-danger-subtle">00:00</span>
            </p>
        </div>

        <div class="d-flex flex-column w-100">
            {{-- Part A TIDAK submit ke server --}}
            <form id="checksheetForm" method="POST" action="javascript:void(0)">
                @csrf
                <div id="form-steps"></div>

                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button type="button" class="btn btn-primary" id="prevBtn">Prev</button>
                    <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                    {{-- Save hanya dipakai di Part B, jadi tetep hidden di Part A --}}
                    <button type="submit" class="btn btn-primary d-none" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="module">
        $(function() {
            const detailId = {{ $detail->id }};
            const PARTB_URL = "{{ route('control.checksheets.partB', $detail) }}";
            const TARGETS_URL = "{{ route('control.checksheets.targets', $detail) }}";
            const COMMIT_URL = "{{ route('control.checksheets.commitTarget', $detail) }}";
            const key = (k) => `cl:${detailId}:${k}`;

            // === Stopwatch persist ===
            if (!sessionStorage.getItem(key('startAt'))) {
                sessionStorage.setItem(key('startAt'), Date.now());
            }
            setInterval(() => {
                const start = Number(sessionStorage.getItem(key('startAt')));
                const sec = Math.floor((Date.now() - start) / 1000);
                const m = String(Math.floor(sec / 60)).padStart(2, '0');
                const s = String(sec % 60).padStart(2, '0');
                $('#stopwatch').text(`${m}:${s}`);
            }, 1000);

            // === Heartbeat keep alive ===
            setInterval(() => {
                $.post("{{ route('control.heartbeat') }}", {
                    _token: '{{ csrf_token() }}'
                });
            }, 45000);

            // === Builder helpers ===
            function renderText(f) {
                return $('<div>', {
                        class: 'mb-2 p-1 border border-2 border-primary rounded-2'
                    })
                    .append(
                        $('<label>', {
                            class: 'form-label',
                            text: f.label
                        }),
                        $('<input>', {
                            type: 'text',
                            class: 'form-control bg-warning-subtle',
                            name: f.name,
                            placeholder: f.placeholder || ''
                        })
                    );
            }

            function renderSelect(f) {
                const $sel = $('<select>', {
                    class: 'form-select bg-warning-subtle',
                    name: f.name
                });
                (f.options || []).forEach(opt => {
                    if (typeof opt === 'string') {
                        $sel.append($('<option>', {
                            value: opt,
                            text: opt
                        }));
                    } else {
                        $sel.append($('<option>', {
                            value: opt.value,
                            text: opt.label
                        }));
                    }
                });
                return $('<div>', {
                        class: 'mb-3 p-2 border border-2 border-primary rounded-3'
                    })
                    .append($('<label>', {
                        class: 'form-label',
                        text: f.label
                    }), $sel);
            }

            function renderRadio(f) {
                const $wrap = $('<div>', {
                        class: 'mb-3 p-2 border border-2 border-primary rounded-3'
                    })
                    .append($('<label>', {
                        class: 'form-label',
                        text: f.label
                    }));
                (f.options || []).forEach(opt => {
                    let val = opt,
                        lbl = opt;
                    if (typeof opt !== 'string') {
                        val = opt.value;
                        lbl = opt.label;
                    }
                    const $opt = $('<div>', {
                            class: 'form-check'
                        })
                        .append(
                            $('<input>', {
                                class: 'form-check-input',
                                type: 'radio',
                                name: f.name,
                                value: val
                            })
                            .on('change', function() {
                                handleConditional(this);
                            }),
                            $('<label>', {
                                class: 'form-check-label',
                                text: lbl
                            })
                        );
                    $wrap.append($opt);
                });
                $wrap.append($('<div>', {
                    id: 'conditional-' + f.name
                }));
                return $wrap;
            }

            function renderDisplay(f) {
                return $('<div>', {
                        class: 'mb-2 p-2 rounded-2 bg-light border'
                    })
                    .append($('<label>', {
                            class: 'form-label d-block mb-0',
                            text: f.label
                        }),
                        $('<div>', {
                            text: f.text || '-'
                        }));
            }

            function renderField(f) {
                if (f.type === 'text') return renderText(f);
                if (f.type === 'select') return renderSelect(f);
                if (f.type === 'radio') return renderRadio(f);
                if (f.type === 'display') return renderDisplay(f);
                return $('<div>', {
                    text: `Unknown: ${f.type}`
                });
            }

            function handleConditional(el) {
                const cond = $(el).data('conditional') || {};
                const target = $('#conditional-' + el.name).empty();
                const val = $(el).val();
                const list = cond[val] || [];
                (list || []).forEach(f => {
                    if (f.type === 'text') target.append(renderText(f));
                    if (f.type === 'radio') target.append(renderRadio(f));
                });
            }

            // === Dynamic loader ===
            let formSteps = [];
            let currentStep = 0;

            function renderStep() {
                const $c = $("#form-steps").empty();
                const step = formSteps[currentStep] || [];
                step.forEach(f => $c.append(renderField(f)));
                $('#prevBtn').toggle(currentStep > 0);
                $('#nextBtn').text(currentStep < formSteps.length - 1 ? 'Next' : 'Lanjut ke Bagian B');
                $('#saveBtn').addClass('d-none');
            }

            $.when(
                $.getJSON("{{ asset('js/form.json') }}"),
                $.getJSON(TARGETS_URL)
            ).done((baseRes, dynRes) => {
                const base = baseRes[0],
                    dyn = dynRes[0];

                // SALIN & bersihkan kandidat lama
                let a1 = Array.isArray(base.bagianA_1) ? [...base.bagianA_1] : [];
                const dropIds = new Set(['operator', 'operator_pick', 'operator_id', 'operator_name',
                    'person_id', 'bagian'
                ]);
                a1 = a1.filter(f => !dropIds.has(f.id));

                // Sisipkan display dept dekat Shift
                const deptDisp = {
                    type: 'display',
                    label: 'Bagian (dari scheduler)',
                    text: dyn.departmentName
                };
                const shiftIdx = a1.findIndex(f => f.id === 'shift');
                if (shiftIdx >= 0) a1.splice(shiftIdx + 1, 0, deptDisp);
                else a1.unshift(deptDisp);

                // Tambahkan field target SESUAI MODE
                if (dyn.mode === 'select_leader') {
                    a1.push({
                        id: 'person_id',
                        type: 'select',
                        name: 'person_id',
                        label: dyn.field.label,
                        options: dyn.options
                    });
                } else if (dyn.mode === 'select_leader_empty') {
                    // nggak ada di jadwal → biarin user pilih manual dari semua Leader? atau kosongkan?
                    // Di sini kita kosongkan select agar user sadar belum ada; kalau mau “semua Leader” tinggal isi dyn.options dengan seluruh Leader.
                    a1.push({
                        id: 'person_id',
                        type: 'select',
                        name: 'person_id',
                        label: 'Leader (belum ada di jadwal tanggal ini)',
                        options: []
                    });
                } else if (dyn.mode === 'locked_leader') {
                    a1.push({
                        type: 'display',
                        label: 'Leader',
                        text: dyn.selected.label
                    });
                } else if (dyn.mode === 'select_operator_from_schedule') {
                    a1.push({
                        id: 'operator_pick',
                        type: 'select',
                        name: 'operator_pick',
                        label: dyn.field.label,
                        options: dyn.options
                    });
                } else if (dyn.mode === 'locked_operator') {
                    a1.push({
                        type: 'display',
                        label: 'Operator',
                        text: dyn.selected.label
                    });
                } else { // manual_operator
                    a1.push({
                        id: 'operator_id',
                        type: 'text',
                        name: 'operator_id',
                        label: 'ID Operator',
                        placeholder: 'OPxxx'
                    });
                    a1.push({
                        id: 'operator_name',
                        type: 'text',
                        name: 'operator_name',
                        label: 'Nama Operator',
                        placeholder: 'Nama Lengkap'
                    });
                }

                // Bagian A_2 (kehadiran) normalisasi ke 0/1
                const a2 = Array.isArray(base.bagianA_2) ? [...base.bagianA_2] : [];
                const hadir = a2.find(f => f.id === 'kehadiran');
                if (hadir) {
                    hadir.options = [{
                        value: '0',
                        label: 'Absen'
                    }, {
                        value: '1',
                        label: 'Hadir'
                    }];
                    const cond = hadir.conditional || {};
                    hadir.conditional = {
                        '0': (cond['0) Absen'] || []),
                        '1': (cond['1) Hadir'] || [])
                    };
                }

                formSteps = [a1, a2].filter(x => x.length);
                currentStep = 0;
                renderStep();
                console.log("base", base);
                console.log("dyn", dyn);
                console.log("a1 before filter", base.bagianA_1);
                console.log("a1 after filter", a1);
            });

            // === Collect Part A & commit target ===
            function collectPartA() {
                const fd = new FormData(document.getElementById('checksheetForm'));
                const out = {
                    shift: fd.get('shift'),
                    attendance: fd.get('kehadiran') || fd.get('attendance')
                };

                if (fd.get('person_id')) out.person_id = fd.get('person_id');

                if (fd.get('operator_pick')) {
                    const raw = fd.get('operator_pick'); // "OP001@@Budi"
                    const [oid, onm] = String(raw).split('@@');
                    out.operator_id = oid || '';
                    out.operator_name = onm || '';
                }

                if (fd.get('operator_id') || fd.get('operator_name')) {
                    out.operator_id = fd.get('operator_id');
                    out.operator_name = fd.get('operator_name');
                }

                return out;
            }


            $('#nextBtn').on('click', function() {
                if (currentStep < formSteps.length - 1) {
                    currentStep++;
                    renderStep();
                    return;
                }

                const partA = collectPartA();
                if (!partA.shift || !partA.attendance) {
                    alert('Lengkapi form');
                    return;
                }

                // Commit target ke backend (idempotent)
                $.ajax({
                    url: COMMIT_URL,
                    method: 'POST',
                    data: {
                        ...partA,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        sessionStorage.setItem(key('partA'), JSON.stringify(partA));
                        const q = new URLSearchParams({
                            attendance: partA.attendance
                        });
                        window.location.href = PARTB_URL + '?' + q.toString();
                    },
                    error: function(xhr) {
                        alert('Commit target gagal: ' + xhr.status);
                    }
                });
            });

            $('#prevBtn').on('click', function() {
                if (currentStep > 0) {
                    currentStep--;
                    renderStep();
                }
            });
        });
    </script>
@endsection
