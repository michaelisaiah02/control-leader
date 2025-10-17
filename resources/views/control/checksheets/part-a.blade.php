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
        <div class="d-flex w-100 mt-1 justify-content-between align-items-center">
            <p class="border border-2 border-white bg-primary rounded-2 text-white py-1 mb-1 px-4 shadow">Bagian A</p>
            <p class="border border-2 border-primary rounded-2 px-2 py-1 mb-1 shadow">Stopwatch:
                <span id="stopwatch" class="mb-1 px-2 text-danger bg-danger-subtle"
                    data-start="{{ $startedAtMs }}">00:00</span>
            </p>
        </div>

        <form id="partA" onsubmit="return false;">
            @csrf
            <input type="hidden" name="schedule_plan_id" value="{{ $plan->id }}">

            {{-- PAGE 1 --}}
            <div id="page1">
                <div class="mb-1 p-2 border border-2 rounded-4">
                    <div class="fw-semibold mb-1">1. Shift</div>
                    <div class="ms-3">
                        @foreach ([1, 2, 3] as $s)
                            <button type="button"
                                class="btn btn-outline-primary me-2 {{ session('shift') == $s ? 'active' : '' }}"
                                {{ session('shift') != $s ? 'disabled' : '' }} readonly>
                                Shift {{ $s }}
                                <input type="hidden" name="shift" value="{{ session('shift') }}">
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mb-1 p-2 border border-2 rounded-4">
                    <div class="fw-semibold mb-1">2. {{ $targetLabel }}</div>
                    <select id="target_pick" name="target_pick" class="form-select bg-warning-subtle" required>
                        <option value="">Pilih...</option>
                        @foreach ($options as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-1 p-2 border border-2 rounded-4">
                    <div class="fw-semibold mb-1">3. Bagian</div>
                    <input type="text" name="bagian" class="form-control bg-warning-subtle" value=""
                        placeholder="Contoh : Cutting" readonly>
                </div>
            </div>

            {{-- PAGE 2 --}}
            <div id="page2" class="d-none">
                <div class="mb-1 p-2 border border-2 rounded-4">
                    <div class="fw-semibold mb-1">4. Check <u>kehadiran</u> operator</div>
                    <div class="ms-3 mb-3 small">
                        <div><u>Apabila operator tidak masuk</u> :</div>
                        <ul class="mb-1">
                            <li>Isi perubahan Man Power di Henkaten Board</li>
                            <li>Operator pengganti harus sesuai Skill Map</li>
                            <li>Konfirmasi hasil awal untuk operator pengganti (Hasil awal kerja OK)</li>
                        </ul>
                        <div>0) <span class="text-decoration-underline">Absen</span></div>
                        <div>1) <span class="text-decoration-underline">Hadir</span></div>
                    </div>

                    <div class="ms-2">
                        <label class="me-4"><input type="radio" name="attendance" value="0"> Absen</label>
                        <label><input type="radio" name="attendance" value="1"> Hadir</label>
                    </div>
                </div>
                <div id="penggantiWrap" class="mb-1 p-2 d-none border border-2 rounded-4">
                    <div class="fw-semibold mb-1">5. Apakah ada operator pengganti?</div>
                    <div class="ms-2">
                        <label class="me-4"><input type="radio" name="ada_pengganti" value="0"> Tidak</label>
                        <label><input type="radio" name="ada_pengganti" value="1"> Ya</label>
                    </div>
                </div>

                <div id="absenWrap" class="d-none">
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-1">Nama Operator <u>Pengganti</u></div>
                        <select id="nama_pengganti" name="nama_pengganti" class="form-select bg-warning-subtle">
                            <option value="">Pilih...</option>
                            @foreach ($options as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-1">Bagian Operator <u>Pengganti</u></div>
                        <input type="text" class="form-control bg-warning-subtle" name="bagian_pengganti"
                            placeholder="Contoh : Finishing" readonly>
                    </div>
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-1"><u>Kondisi</u> Operator Pengganti</div>
                        <div class="ms-2">
                            <label class="me-4"><input type="radio" name="kondisi_pengganti" value="Sehat">
                                Sehat</label>
                            <label><input type="radio" name="kondisi_pengganti" value="Sakit"> Sakit</label>
                        </div>
                    </div>
                </div>

                <div id="hadirWrap" class="d-none">
                    <div class="mb-3 p-3 border rounded-4">
                        <div class="fw-semibold mb-1"><u>Kondisi</u> Operator</div>
                        <div class="ms-2">
                            <label class="me-4"><input type="radio" name="kondisi" value="Sehat"> Sehat</label>
                            <label><input type="radio" name="kondisi" value="Sakit"> Sakit</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-3">
                <button type="button" class="btn btn-primary d-none" id="prevBtn">Back</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')

    <script type="module">
        $(function() {
            const PHASE = @json($phase);
            const PLAN = {{ $plan->id }};
            const DASHBOARD_URL = @json(route('dashboard'));
            const PARTB_URL = @json(route('control.checksheets.partB'));
            const START_URL = @json(route('control.drafts.start'));
            const HEART_URL = @json(route('control.heartbeat'));
            const key = (k) => `cl:plan:${PLAN}:phase:${PHASE}:${k}`;

            // start draft (server) → dapat started_at_ms
            $.post(START_URL, {
                    _token: '{{ csrf_token() }}',
                    schedule_plan_id: PLAN,
                    phase: PHASE
                })
                .done(r => {
                    if (r.started_at_ms) {
                        $('#stopwatch').data('start', r.started_at_ms);
                    }
                    tick();
                    setInterval(tick, 1000);
                });

            function tick() {
                const started = parseInt($('#stopwatch').data('start'), 10);
                const sec = Math.floor((Date.now() - started) / 1000);
                const m = String(Math.floor(sec / 60)).padStart(2, '0');
                const s = String(sec % 60).padStart(2, '0');
                $('#stopwatch').text(`${m}:${s}`);
            }

            setInterval(() => $.post(HEART_URL, {
                _token: '{{ csrf_token() }}'
            }), 45000);

            let page = 1;
            $('#prevBtn').toggleClass('d-none', page === 1);

            $('[name="attendance"]').on('change', function() {
                const v = $(this).val();
                $('#penggantiWrap').toggleClass('d-none', v !== '0');
                $('#hadirWrap').toggleClass('d-none', v !== '1');
                // Clear pengganti fields when "Hadir" is selected
                if (v === '1') {
                    $('input[name="ada_pengganti"]').prop('checked', false);
                    $('input[name="nama_pengganti"]').val('');
                    $('input[name="bagian_pengganti"]').val('');
                    $('input[name="kondisi_pengganti"]').prop('checked', false);
                }
                // Uncheck kondisi operator when absen is selected
                if (v === '0') {
                    $('input[name="kondisi"]').prop('checked', false);
                }
            });

            $('[name="ada_pengganti"]').on('change', function() {
                const v = $(this).val();
                $('#absenWrap').toggleClass('d-none', v !== '1');

                // Clear pengganti fields when "Tidak" is selected
                if (v === '0') {
                    $('select[name="nama_pengganti"]').selectize()[0].selectize.clear();
                    $('input[name="bagian_pengganti"]').val('');
                    $('input[name="kondisi_pengganti"]').prop('checked', false);
                }
            });

            const shift = $('input[name="shift"]').val();

            $('#prevBtn').on('click', function() {
                if (page === 2) {
                    $('#page2').addClass('d-none');
                    $('#page1').removeClass('d-none');
                    page = 1;
                    $('#prevBtn').toggleClass('d-none', page === 1);
                }
            });

            $('#nextBtn').on('click', function() {
                const target = $('[name="target_pick"]').val();
                const bagian = $('[name="bagian"]').val();
                const attend = $('input[name="attendance"]:checked').val();
                const adaPengganti = $('input[name="ada_pengganti"]:checked').val();
                const kondisi = $('input[name="kondisi"]:checked').val();
                const namaPengganti = $('select[name="nama_pengganti"]').val();
                const bagianPengganti = $('input[name="bagian_pengganti"]').val();
                const kondisiPengganti = $('input[name="kondisi_pengganti"]:checked').val();
                console.log(target, bagian, attend, adaPengganti, kondisi, namaPengganti, bagianPengganti,
                    kondisiPengganti);
                if (page === 1) {
                    if (!target || !bagian) {
                        return alert('Lengkapi form.');
                    }
                    $('#page1').addClass('d-none');
                    $('#page2').removeClass('d-none');
                    page = 2;
                    $('#prevBtn').toggleClass('d-none', page === 1);
                    return;
                }

                // simpan Part A (client)
                if (!attend) {
                    return alert('Lengkapi form.');
                }
                const payload = {
                    shift,
                    target,
                    bagian,
                    attendance: attend,
                    has_replacement: adaPengganti,
                    kondisi: kondisi || null,
                    nama_pengganti: namaPengganti || null,
                    bagian_pengganti: bagianPengganti || null,
                    kondisi_pengganti: kondisiPengganti || null,
                };
                sessionStorage.setItem(key('partA'), JSON.stringify(payload));

                // Kalau tidak ada pengganti, langsung submit form
                if (attend === '0' && adaPengganti === '0') {
                    // Update payload structure to match server validation
                    const finalPayload = {
                        _token: '{{ csrf_token() }}',
                        schedule_plan_id: PLAN,
                        phase: PHASE,
                        part_a: {
                            shift: parseInt(shift),
                            target: target,
                            division: bagian,
                            attendance: parseInt(attend),
                            ada_pengganti: adaPengganti || '0',
                            kondisi: kondisi || null,
                            nama_pengganti: namaPengganti || null,
                            bagian_pengganti: bagianPengganti || null,
                            kondisi_pengganti: kondisiPengganti || null
                        }
                    };
                    $.post(@json(route('control.checksheets.store')) + `?type=${PHASE}`, finalPayload)
                        .done((message) => {
                            // hapus session draft
                            sessionStorage.removeItem(key('partA'));
                            // ke Dashboard
                            const url = `${DASHBOARD_URL}`;
                            window.location.href = url;
                        }).fail((xhr) => {
                            console.error('Server error:', xhr.responseJSON);
                            alert('Gagal menyimpan data. Silakan coba lagi.');
                            return false;
                        });
                } else if (attend === '0' && adaPengganti === '1') {
                    if (!namaPengganti || !bagianPengganti || !kondisiPengganti) {
                        return alert('Lengkapi form.');
                    }
                    payload.has_replacement = true;
                } else {
                    // attend === '1'
                    if (!payload.kondisi) {
                        return alert('Lengkapi form.');
                    }
                    payload.has_replacement = false;
                }
                // ke Part B
                const url = `${PARTB_URL}?type=${encodeURIComponent(PHASE)}&plan=${PLAN}`;
                window.location.href = url;
            });

            // restore bila balik dari Part B
            try {
                const earlier = JSON.parse(sessionStorage.getItem(key('partA')) || 'null');
                if (earlier) {
                    $(`input[name="shift"][value="${earlier.shift}"]`).prop('checked', true);
                    $('[name="target_pick"]').val(earlier.target);
                    $('[name="bagian"]').val(earlier.bagian);
                    $(`input[name="attendance"][value="${earlier.attendance}"]`).prop('checked', true)
                        .trigger(
                            'change');
                    if (earlier.attendance === '0') {
                        $(`input[name="ada_pengganti"][value="${earlier.has_replacement ? '1' : '0'
                            }"]`).prop('checked', true).trigger('change');
                    }
                    if (earlier.nama_pengganti) $('input[name="nama_pengganti"]').val(earlier
                        .nama_pengganti);
                    if (earlier.bagian_pengganti) $('input[name="bagian_pengganti"]').val(earlier
                        .bagian_pengganti);
                    if (earlier.kondisi_pengganti) $(
                        `input[name="kondisi_pengganti"][value="${earlier.kondisi_pengganti}"]`).prop(
                        'checked',
                        true);
                    if (earlier.kondisi) $(`input[name="kondisi"][value="${earlier.kondisi}"]`).prop(
                        'checked',
                        true);
                }
            } catch (e) {}
        });
        $(document).ready(function() {
            let targetSelectize = $('[name="target_pick"]').selectize({
                theme: 'bootstrap5'
            })[0].selectize;

            let penggantiSelectize = $('[name="nama_pengganti"]').selectize({
                theme: 'bootstrap5'
            })[0].selectize;

            // Store original options for pengganti
            let originalPenggantiOptions = Object.values(penggantiSelectize.options);

            // Update pengganti options when target changes
            $('[name="target_pick"]').on('change', function() {
                const selectedValue = $(this).val();

                // Update bagian field
                if (selectedValue) {
                    const parts = selectedValue.split('::');
                    const bagian = parts[parts.length - 1];
                    $('[name="bagian"]').val(bagian);
                } else {
                    $('[name="bagian"]').val('');
                }

                // Clear pengganti selectize and rebuild options
                penggantiSelectize.clearOptions();
                penggantiSelectize.clear();

                // Add back all original options except the selected one
                Object.values(originalPenggantiOptions).forEach(option => {
                    if (option.value !== selectedValue) {
                        penggantiSelectize.addOption(option);
                    }
                });

                penggantiSelectize.refreshOptions();
            });

            $('[name="nama_pengganti"]').on('change', function() {
                const selectedValue = $(this).val();
                if (selectedValue) {
                    const parts = selectedValue.split('::');
                    const bagian = parts[parts.length - 1];
                    $('[name="bagian_pengganti"]').val(bagian);
                } else {
                    $('[name="bagian_pengganti"]').val('');
                }
            });
        });
    </script>
    @session('info')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toastLiveExample = document.getElementById('infoNotification');
                if (toastLiveExample) {
                    const toast = new bootstrap.Toast(toastLiveExample);
                    toast.show();
                }
            });
        </script>
    @endsession
@endsection
