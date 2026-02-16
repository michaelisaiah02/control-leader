@extends('layouts.app')

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-3 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 border border-light text-white animate-fade-in subtitle">
        <i class="bi bi-ui-checks-grid me-2 fs-5"></i>
        <span class="fs-5 fw-bold text-uppercase">
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
        </span>
    </div>
@endpush

@section('content')
    <div class="container-fluid max-w-800 mx-auto">

        {{-- HEADER INFO --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="badge bg-primary fs-6 px-3 py-2 shadow-sm rounded-pill">
                <i class="bi bi-file-earmark-text me-1"></i> Bagian A
            </div>

            <div class="badge bg-white text-dark border shadow-sm px-3 py-2 rounded-pill fs-6">
                <i class="bi bi-stopwatch text-danger me-1"></i> Timer:
                <span id="stopwatch" class="text-danger fw-bold ms-1" data-start="{{ $startedAtMs }}">00:00</span>
            </div>
        </div>

        {{-- PROGRESS WIZARD --}}
        <div class="progress mb-2" style="height: 4px;">
            <div class="progress-bar bg-primary transition-all" id="wizard-progress" role="progressbar" style="width: 50%;">
            </div>
        </div>

        {{-- FORM START --}}
        <form id="partA" onsubmit="return false;" class="position-relative">
            @csrf
            <input type="hidden" name="schedule_plan_id" value="{{ $plan->id }}">

            {{-- =====================================
             PAGE 1: IDENTITAS
             ===================================== --}}
            <div id="page1" class="animate-fade-in">
                <div class="card border-0 shadow-sm rounded-4 mb-2">
                    <div class="card-header bg-light border-bottom-0 pt-3 pb-2 rounded-top-4">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-1-circle me-2"></i>Informasi Target</h6>
                    </div>
                    <div class="card-body p-3">

                        {{-- 1. Shift --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">1. Shift Kerja</label>
                            <div class="d-flex gap-2">
                                @foreach ([1, 2, 3] as $s)
                                    <button type="button"
                                        class="btn btn-outline-primary flex-fill fw-bold {{ session('shift') == $s ? 'active' : '' }}"
                                        {{ session('shift') != $s ? 'disabled' : '' }}>
                                        Shift {{ $s }}
                                        @if (session('shift') == $s)
                                            <i class="bi bi-check-circle-fill ms-1"></i>
                                        @endif
                                    </button>
                                @endforeach
                                <input type="hidden" name="shift" value="{{ session('shift') }}">
                            </div>
                        </div>

                        {{-- 2. Target Pick --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">2.
                                {{ $targetLabel }}</label>
                            <select id="target_pick" name="target_pick" placeholder="Pilih {{ $targetLabel }}..." required>
                                <option value="">Pilih {{ $targetLabel }}...</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Bagian --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">3. Bagian
                                (Auto-fill)</label>
                            <input type="text" name="bagian" class="form-control bg-light" placeholder="-" readonly>
                        </div>

                    </div>
                </div>
            </div>


            {{-- =====================================
             PAGE 2: KEHADIRAN
             ===================================== --}}
            <div id="page2" class="d-none animate-fade-in">
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-light border-bottom-0 pt-3 pb-2 rounded-top-4">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-2-circle me-2"></i>Status Kehadiran</h6>
                    </div>

                    <div class="card-body p-3">

                        {{-- 4. Check Kehadiran --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark fs-5 mb-3">Apakah operator hadir?</label>

                            {{-- Hint Box --}}
                            <div class="alert alert-warning border-warning-subtle small py-2 mb-3">
                                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Jika Absen:</strong>
                                <ul class="mb-0 ps-3 mt-1 text-muted">
                                    <li>Isi perubahan Man Power di Henkaten Board</li>
                                    <li>Operator pengganti harus sesuai Skill Map</li>
                                    <li>Konfirmasi hasil awal pengganti (Hasil OK)</li>
                                </ul>
                            </div>

                            {{-- Custom Radio Buttons as Blocks --}}
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="attendance" id="att_hadir" value="1">
                                    <label class="btn btn-outline-success w-100 py-2 fw-bold rounded-3" for="att_hadir">
                                        <i class="bi bi-person-check fs-4 d-block mb-1"></i> Hadir
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="attendance" id="att_absen" value="0">
                                    <label class="btn btn-outline-danger w-100 py-2 fw-bold rounded-3" for="att_absen">
                                        <i class="bi bi-person-x fs-4 d-block mb-1"></i> Absen
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- === BILA HADIR === --}}
                        <div id="hadirWrap" class="d-none bg-light p-3 rounded-3 border mb-3 animate-fade-in">
                            <label class="form-label fw-bold text-dark mb-2">Kondisi Operator saat ini:</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="kondisi" id="kondisi_sehat"
                                        value="Sehat">
                                    <label class="form-check-label text-success fw-bold" for="kondisi_sehat">Sehat</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="kondisi" id="kondisi_sakit"
                                        value="Sakit">
                                    <label class="form-check-label text-danger fw-bold" for="kondisi_sakit">Sakit</label>
                                </div>
                            </div>
                        </div>

                        {{-- === BILA ABSEN === --}}
                        <div id="penggantiWrap" class="d-none mb-3 animate-fade-in">
                            <label class="form-label fw-bold text-dark mb-2">Apakah ada operator pengganti?</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="ada_pengganti" id="pengganti_ya"
                                        value="1">
                                    <label class="btn btn-outline-primary w-100 py-2 fw-bold" for="pengganti_ya">Ya,
                                        Ada</label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="ada_pengganti" id="pengganti_tidak"
                                        value="0">
                                    <label class="btn btn-outline-secondary w-100 py-2 fw-bold"
                                        for="pengganti_tidak">Tidak Ada</label>
                                </div>
                            </div>
                        </div>

                        {{-- === FORM PENGGANTI (Muncul jika ada pengganti) === --}}
                        <div id="absenWrap" class="d-none bg-light p-3 rounded-3 border animate-fade-in">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Data Operator Pengganti</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Nama
                                    Pengganti</label>
                                <select id="nama_pengganti" name="nama_pengganti" placeholder="Cari Nama Operator...">
                                    <option value="">Cari Nama Operator...</option>
                                    @foreach ($options as $opt)
                                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Bagian
                                    Pengganti</label>
                                <input type="text" class="form-control bg-white" name="bagian_pengganti"
                                    placeholder="-" readonly>
                            </div>

                            <div class="mb-1">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Kondisi
                                    Pengganti</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kondisi_pengganti"
                                            id="kp_sehat" value="Sehat">
                                        <label class="form-check-label text-success fw-bold" for="kp_sehat">Sehat</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kondisi_pengganti"
                                            id="kp_sakit" value="Sakit">
                                        <label class="form-check-label text-danger fw-bold" for="kp_sakit">Sakit</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- STICKY ACTION BAR (Nempel di bawah) --}}
    <div class="action-bar d-flex justify-content-between align-items-center px-3 px-md-5 bg-white border-top shadow-lg"
        style="z-index: 1030;">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold d-none" id="prevBtn">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </button>
        {{-- Spacing filler saat tombol back hide --}}
        <div id="prevSpacer" style="width: 100px;"></div>

        <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm ms-auto" id="nextBtn">
            Lanjut <i class="bi bi-arrow-right ms-2" id="nextIcon"></i>
        </button>
    </div>

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(function() {
            const PHASE = @json($phase);
            const PLAN = {{ $plan->id }};
            const DASHBOARD_URL = @json(route('dashboard'));
            const PARTB_URL = @json(route('checksheets.partB'));
            const START_URL = @json(route('checksheets.drafts.start'));
            const HEART_URL = @json(route('checksheets.heartbeat'));
            const key = (k) => `cl:plan:${PLAN}:phase:${PHASE}:${k}`;

            // Toast Helper Function
            function showToast(message, type = 'info') {
                const toastHTML = `
                    <div class="toast position-fixed top-0 start-50 translate-middle-x mt-3 align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                const toastContainer = document.getElementById('toastContainer') || (() => {
                    const container = document.createElement('div');
                    container.id = 'toastContainer';
                    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                    document.body.appendChild(container);
                    return container;
                })();
                const toastEl = document.createElement('div');
                toastEl.innerHTML = toastHTML;
                toastContainer.appendChild(toastEl.firstElementChild);
                const toast = new bootstrap.Toast(toastContainer.lastElementChild);
                toast.show();
            }

            // --- 1. TIMER & HEARTBEAT ---
            $.post(START_URL, {
                _token: '{{ csrf_token() }}',
                schedule_plan_id: PLAN,
                phase: PHASE
            }).done(r => {
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

            // --- 2. SELECTIZE INIT ---
            let targetSelectize = $('[name="target_pick"]').selectize({
                theme: 'bootstrap5',
                dropdownParent: 'body'
            })[0].selectize;

            let penggantiSelectize = $('[name="nama_pengganti"]').selectize({
                theme: 'bootstrap5',
                dropdownParent: 'body'
            })[0].selectize;
            let originalPenggantiOptions = Object.values(penggantiSelectize.options);

            // --- 3. LOGIC FIELD DEPENDENCY ---
            $('[name="target_pick"]').on('change', function() {
                const selectedValue = $(this).val();
                if (selectedValue) {
                    const parts = selectedValue.split('::');
                    const bagian = parts[parts.length - 1];
                    $('[name="bagian"]').val(bagian);
                } else {
                    $('[name="bagian"]').val('');
                }

                penggantiSelectize.clearOptions();
                penggantiSelectize.clear();
                Object.values(originalPenggantiOptions).forEach(option => {
                    if (option.value !== selectedValue) {
                        penggantiSelectize.addOption(option);
                    }
                });
                penggantiSelectize.refreshOptions();
            });

            $('[name="nama_pengganti"]').on('change', function() {
                const val = $(this).val();
                $('[name="bagian_pengganti"]').val(val ? val.split('::').pop() : '');
            });

            // --- 4. RADIO LOGIC (UX Form) ---
            $('[name="attendance"]').on('change', function() {
                const v = $(this).val();
                $('#penggantiWrap').toggleClass('d-none', v !== '0');
                $('#hadirWrap').toggleClass('d-none', v !== '1');

                if (v === '1') {
                    $('input[name="ada_pengganti"]').prop('checked', false).trigger('change');
                } else {
                    $('input[name="kondisi"]').prop('checked', false);
                }
            });

            $('[name="ada_pengganti"]').on('change', function() {
                const v = $(this).val();
                $('#absenWrap').toggleClass('d-none', v !== '1');

                if (v !== '1') {
                    penggantiSelectize.clear();
                    $('input[name="bagian_pengganti"]').val('');
                    $('input[name="kondisi_pengganti"]').prop('checked', false);
                }
            });

            // --- 5. NAVIGATION WIZARD ---
            let page = 1;
            const shift = $('input[name="shift"]').val();

            function updateWizardUI() {
                if (page === 1) {
                    $('#page2').addClass('d-none');
                    $('#page1').removeClass('d-none');
                    $('#prevBtn').addClass('d-none');
                    $('#prevSpacer').removeClass('d-none');
                    $('#wizard-progress').css('width', '50%');
                    $('#nextBtn').html('Lanjut <i class="bi bi-arrow-right ms-2" id="nextIcon"></i>').removeClass(
                        'btn-success').addClass('btn-primary');
                } else {
                    $('#page1').addClass('d-none');
                    $('#page2').removeClass('d-none');
                    $('#prevBtn').removeClass('d-none');
                    $('#prevSpacer').addClass('d-none');
                    $('#wizard-progress').css('width', '100%');
                    $('#nextBtn').html('<i class="bi bi-check-lg me-2"></i> Submit').removeClass('btn-primary')
                        .addClass('btn-success');
                }
            }

            $('#prevBtn').on('click', function() {
                if (page === 2) {
                    page = 1;
                    updateWizardUI();
                }
            });

            $('#nextBtn').on('click', function() {
                const target = $('[name="target_pick"]').val();
                const bagian = $('[name="bagian"]').val();

                // Validation Page 1
                if (page === 1) {
                    if (!target || !bagian) {
                        showToast('Silakan lengkapi pilihan target.', 'warning');
                        return;
                    }
                    page = 2;
                    updateWizardUI();
                    return;
                }

                // Validation & Submit Page 2
                const attend = $('input[name="attendance"]:checked').val();
                if (!attend) {
                    showToast('Silakan isi status kehadiran.', 'warning');
                    return;
                }

                const adaPengganti = $('input[name="ada_pengganti"]:checked').val();
                const kondisi = $('input[name="kondisi"]:checked').val();
                const namaPengganti = $('select[name="nama_pengganti"]').val();
                const bagianPengganti = $('input[name="bagian_pengganti"]').val();
                const kondisiPengganti = $('input[name="kondisi_pengganti"]:checked').val();

                // Prepare Payload Storage
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

                // Logic Submit Final
                if (attend === '0' && adaPengganti === '0') {
                    const btn = $(this);
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                    const finalPayload = {
                        _token: '{{ csrf_token() }}',
                        schedule_plan_id: PLAN,
                        phase: PHASE,
                        part_a: {
                            shift: parseInt(shift),
                            target: target,
                            division: bagian,
                            attendance: parseInt(attend),
                            has_replacement: '0',
                            kondisi: null,
                            nama_pengganti: null,
                            bagian_pengganti: null,
                            kondisi_pengganti: null
                        }
                    };

                    $.ajax({
                        url: @json(route('checksheets.store')) + `?type=${PHASE}`,
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(finalPayload),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json' // <--- Paksa Laravel return error JSON, bukan redirect
                        },
                        success: function() {
                            sessionStorage.removeItem(key('partA'));
                            console.log('ok');
                            window.location.href = DASHBOARD_URL;
                        },
                        error: function(xhr) {
                            console.error('Validation Error:', xhr.responseJSON);
                            alert('Gagal menyimpan: Cek kembali isian Anda.');
                            btn.prop('disabled', false).html(
                                '<i class="bi bi-check-lg me-2"></i> Submit');
                        }
                    });

                } else {
                    // Harus isi part B
                    if (attend === '0' && adaPengganti === '1') {
                        if (!namaPengganti || !bagianPengganti || !kondisiPengganti) {
                            showToast('Lengkapi data operator pengganti.', 'warning');
                            return;
                        }
                        payload.has_replacement = true;
                    } else if (attend === '1') {
                        if (!kondisi) {
                            showToast('Pilih kondisi operator.', 'warning');
                            return;
                        }
                        payload.has_replacement = false;
                    }

                    // Pindah halaman
                    window.location.href = `${PARTB_URL}?type=${encodeURIComponent(PHASE)}&plan=${PLAN}`;
                }
            });

            // --- 6. RESTORE SESSION DATA (Balik dari Part B) ---
            try {
                const earlier = JSON.parse(sessionStorage.getItem(key('partA')) || 'null');
                if (earlier) {
                    // Set page to 2 immediately because data is filled
                    page = 2;
                    updateWizardUI();

                    // Wait for selectize to be ready before setting value
                    setTimeout(() => {
                        targetSelectize.setValue(earlier.target);
                        $('[name="bagian"]').val(earlier.bagian);

                        $(`input[name="attendance"][value="${earlier.attendance}"]`).prop('checked', true)
                            .trigger('change');

                        if (earlier.attendance === '0') {
                            $(`input[name="ada_pengganti"][value="${earlier.has_replacement === true || earlier.has_replacement === '1' ? '1' : '0'}"]`)
                                .prop('checked', true).trigger('change');
                        }

                        if (earlier.nama_pengganti) penggantiSelectize.setValue(earlier.nama_pengganti);
                        if (earlier.bagian_pengganti) $('input[name="bagian_pengganti"]').val(earlier
                            .bagian_pengganti);
                        if (earlier.kondisi_pengganti) $(
                                `input[name="kondisi_pengganti"][value="${earlier.kondisi_pengganti}"]`)
                            .prop('checked', true);
                        if (earlier.kondisi) $(`input[name="kondisi"][value="${earlier.kondisi}"]`).prop(
                            'checked', true);
                    }, 100);
                }
            } catch (e) {
                console.error('Restore session failed:', e)
            }
        });
    </script>

    @session('info')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toastEl = document.getElementById('infoNotification');
                if (toastEl) new bootstrap.Toast(toastEl).show();
            });
        </script>
    @endsession
@endsection
