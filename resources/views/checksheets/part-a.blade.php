@extends('layouts.app')

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
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
                    CONTROL LEADER
            @endswitch
        </span>
    </div>
@endpush

@section('content')
    <div class="container-fluid max-w-800 mx-auto mb-4 pb-5">

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
        @if ($phase !== 'leader')
            <div class="progress mb-2" style="height: 4px;">
                <div class="progress-bar bg-primary transition-all" id="wizard-progress" role="progressbar"
                    style="width: 50%;"></div>
            </div>
        @endif

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
                        @if ($phase !== 'leader')
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
                        @endif

                        {{-- 2. Target Pick --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">
                                {{ $phase === 'leader' ? '1' : '2' }}. {{ $targetLabel }}
                            </label>
                            <select id="target_pick" name="target_pick" placeholder="Pilih {{ $targetLabel }}..." required>
                                <option value="">Pilih {{ $targetLabel }}...</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Bagian --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">
                                {{ $phase === 'leader' ? '2' : '3' }}. Bagian
                            </label>
                            <input type="text" name="bagian" class="form-control bg-light" placeholder="-" readonly>
                        </div>

                    </div>
                </div>
            </div>


            {{-- =====================================
             PAGE 2: KEHADIRAN
             ===================================== --}}
            @if ($phase !== 'leader')
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
                                        <input type="radio" class="btn-check" name="attendance" id="att_hadir"
                                            value="1">
                                        <label class="btn btn-outline-success w-100 py-2 fw-bold rounded-3" for="att_hadir">
                                            <i class="bi bi-person-check fs-4 d-block mb-1"></i> Hadir
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="attendance" id="att_absen"
                                            value="0">
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
                                        <label class="form-check-label text-success fw-bold"
                                            for="kondisi_sehat">Sehat</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kondisi" id="kondisi_sakit"
                                            value="Sakit">
                                        <label class="form-check-label text-danger fw-bold"
                                            for="kondisi_sakit">Sakit</label>
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
                                        <input type="radio" class="btn-check" name="ada_pengganti"
                                            id="pengganti_tidak" value="0">
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
                                    <select id="nama_pengganti" name="nama_pengganti"
                                        placeholder="Cari Nama Operator...">
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
                                            <label class="form-check-label text-success fw-bold"
                                                for="kp_sehat">Sehat</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="kondisi_pengganti"
                                                id="kp_sakit" value="Sakit">
                                            <label class="form-check-label text-danger fw-bold"
                                                for="kp_sakit">Sakit</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>

    {{-- STICKY ACTION BAR (Nempel di bawah) --}}
    <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2 fw-bold d-none" id="prevBtn">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </button>
        <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-bold" id="cancelBtn"
            data-bs-toggle="modal" data-bs-target="#cancelModal">
            <i class="bi bi-x-lg me-2"></i> Batal
        </button>


        @if ($phase === 'leader')
            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow-sm ms-auto"
                id="nextBtn">
                <i class="bi bi-check-lg me-2"></i> Submit
            </button>
        @else
            <button type="button" class="btn btn-outline-primary rounded-pill px-5 py-2 fw-bold shadow-sm ms-auto"
                id="nextBtn">
                Lanjut <i class="bi bi-arrow-right ms-2" id="nextIcon"></i>
            </button>
        @endif
    </div>

    {{-- MODAL KONFIRMASI BATAL --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 mt-2">
                    <h5 class="modal-title fw-bold text-danger" id="cancelModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Batal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-secondary px-4 py-3">
                    Apakah Anda yakin ingin membatalkan pengisian checksheet? <br><br>
                    <span class="text-dark fw-bold">Semua data yang sudah diisi akan hilang dan tidak dapat
                        dikembalikan.</span>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Lanjut
                        Isi</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
                        id="confirmCancelBtn">Ya, Batalkan</button>
                </div>
            </div>
        </div>
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
            const key = (k) => `cl:plan:${PLAN}:phase:${PHASE}:${k}`;

            // Toast Helper
            function showToast(message, type = 'info') {
                const toastHTML = `
                <div class="toast position-fixed top-0 start-50 translate-middle-x mt-3 align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999;">
                    <div class="d-flex">
                        <div class="toast-body fw-bold">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
                let toastContainer = document.getElementById('toastContainer');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toastContainer';
                    document.body.appendChild(toastContainer);
                }
                const toastEl = $(toastHTML).appendTo(toastContainer);
                const toast = new bootstrap.Toast(toastEl[0], {
                    delay: 3000
                });
                toast.show();
                toastEl.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // --- 1. TIMER MURNI DARI DATA BLADE ---
            const started = parseInt($('#stopwatch').data('start'), 10);

            function tick() {
                const sec = Math.floor((Date.now() - started) / 1000);
                const m = String(Math.floor(sec / 60)).padStart(2, '0');
                const s = String(sec % 60).padStart(2, '0');
                $('#stopwatch').text(`${m}:${s}`);
            }
            tick();
            setInterval(tick, 1000);

            // --- 2. SELECTIZE INIT ---
            let targetSelectize = $('[name="target_pick"]').selectize({
                theme: 'bootstrap5',
                dropdownParent: 'body'
            })[0].selectize;

            let penggantiSelectize = null;
            let originalPenggantiOptions = [];

            if (PHASE !== 'leader') {
                penggantiSelectize = $('[name="nama_pengganti"]').selectize({
                    theme: 'bootstrap5',
                    dropdownParent: 'body'
                })[0].selectize;
                originalPenggantiOptions = Object.values(penggantiSelectize.options);
            }

            $('[name="target_pick"]').on('change', function() {
                const selectedValue = $(this).val();
                console.log(selectedValue);
                if (selectedValue) {
                    const parts = selectedValue.split('::');
                    if (parts.length >= 3) {
                        $('[name="bagian"]').val(parts[2]);
                    } else {
                        $('[name="bagian"]').val('');
                    }
                } else {
                    $('[name="bagian"]').val('');
                }
                if (PHASE !== 'leader' && penggantiSelectize) {
                    const currentPenggantiVal = penggantiSelectize.getValue();
                    penggantiSelectize.clearOptions();
                    originalPenggantiOptions.forEach(opt => {
                        if (opt.value === "" || opt.value !== selectedValue) {
                            penggantiSelectize.addOption(opt);
                        }
                    });
                    if (currentPenggantiVal && penggantiSelectize.options[currentPenggantiVal]) {
                        penggantiSelectize.setValue(currentPenggantiVal);
                    }
                }
            });

            $('[name="nama_pengganti"]').on('change', function() {
                const val = $(this).val();
                if (val) {
                    const parts = val.split('::');
                    $('[name="bagian_pengganti"]').val(parts.length >= 3 ? parts[2] : '');
                } else {
                    $('[name="bagian_pengganti"]').val('');
                }
            });

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
                    $('#nama_pengganti').val('').trigger('change');
                    $('input[name="bagian_pengganti"]').val('');
                    $('input[name="kondisi_pengganti"]').prop('checked', false);
                }
            });

            let page = 1;
            const shift = $('input[name="shift"]').val();

            function updateWizardUI() {
                if (page === 1) {
                    $('#page2').addClass('d-none');
                    $('#page1').removeClass('d-none');
                    $('#prevBtn').addClass('d-none');
                    $('#wizard-progress').css('width', '50%');
                    $('#nextBtn').html('Lanjut <i class="bi bi-arrow-right ms-2" id="nextIcon"></i>').removeClass(
                        'btn-success').addClass('btn-outline-primary');
                } else {
                    $('#page1').addClass('d-none');
                    $('#page2').removeClass('d-none');
                    $('#prevBtn').removeClass('d-none');
                    $('#wizard-progress').css('width', '100%');
                    $('#nextBtn').html('<i class="bi bi-check-lg me-2"></i> Submit').removeClass(
                            'btn-outline-primary')
                        .addClass('btn-success');
                }
            }

            // --- LOGIC TOMBOL BATAL (VIA MODAL) ---
            $('#confirmCancelBtn').on('click', async function() {
                const btn = $(this);
                const cancelText = btn.html();

                // Ubah tombol jadi loading state
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Membatalkan...');

                try {
                    // Tembak API ke server buat buka gembok session Laravel
                    const res = await fetch('{{ route('checksheets.cancel') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            plan_id: PLAN,
                            phase: PHASE
                        })
                    });

                    if (res.ok) {
                        // Tutup modal secara programatik (opsional, tapi biar smooth)
                        const modalEl = document.getElementById('cancelModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        // Hapus draft browser dan pulang ke Dashboard!
                        sessionStorage.removeItem(key('partA'));
                        window.location.href = DASHBOARD_URL;
                    } else {
                        alert('Gagal membatalkan. Silakan coba lagi.');
                        btn.prop('disabled', false).html(cancelText);
                    }
                } catch (err) {
                    alert('Terjadi kesalahan koneksi saat membatalkan.');
                    btn.prop('disabled', false).html(cancelText);
                }
            });

            $('#prevBtn').on('click', function() {
                if (page === 2) {
                    page = 1;
                    updateWizardUI();
                }
            });

            $('#nextBtn').on('click', function() {
                const target = $('[name="target_pick"]').val();
                const bagian = $('[name="bagian"]').val();

                if (!target) return showToast('Silakan pilih target terlebih dahulu.', 'warning');

                // === LOGIC UNTUK LEADER (SUPERVISOR NGECEK) ===
                if (PHASE === 'leader') {
                    // Simpan data target ke session aja, gausah langsung tembak API
                    const payload = {
                        shift: null,
                        target: target,
                        bagian: bagian,
                        attendance: '1', // Hardcode hadir
                        has_replacement: '0'
                    };
                    sessionStorage.setItem(key('partA'), JSON.stringify(payload));

                    // Langsung lempar ke Part B biar bisa ngisi jawaban!
                    window.location.href = `${PARTB_URL}?type=${encodeURIComponent(PHASE)}&plan=${PLAN}`;
                    return;
                }

                // === LOGIKA WIZARD (SELAIN LEADER) ===
                if (page === 1) {
                    page = 2;
                    updateWizardUI();
                    return;
                }

                const attend = $('input[name="attendance"]:checked').val();
                if (!attend) return showToast('Silakan pilih status kehadiran.', 'warning');

                const adaPengganti = $('input[name="ada_pengganti"]:checked').val();
                const kondisi = $('input[name="kondisi"]:checked').val();
                const namaPengganti = $('select[name="nama_pengganti"]').val();
                const bagianPengganti = $('input[name="bagian_pengganti"]').val();
                const kondisiPengganti = $('input[name="kondisi_pengganti"]:checked').val();

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

                    $.post(@json(route('checksheets.store')) + `?type=${PHASE}`, finalPayload)
                        .done((res) => {
                            sessionStorage.removeItem(key('partA'));
                            window.location.href = res.redirect || DASHBOARD_URL;
                        }).fail((xhr) => {
                            console.error('Validation Error:', xhr.responseJSON);
                            showToast('Gagal menyimpan. Terjadi kesalahan koneksi.', 'error');
                            btn.prop('disabled', false).html(
                                '<i class="bi bi-check-lg me-2"></i> Submit');
                        });

                } else {
                    if (attend === '0' && adaPengganti === '1') {
                        if (!namaPengganti || !kondisiPengganti) return showToast(
                            'Lengkapi data operator pengganti.', 'warning');
                        payload.has_replacement = true;
                    } else if (attend === '1') {
                        if (!kondisi) return showToast('Pilih kondisi saat ini.', 'warning');
                        payload.has_replacement = false;
                    }
                    window.location.href = `${PARTB_URL}?type=${encodeURIComponent(PHASE)}&plan=${PLAN}`;
                }
            });

            // --- 5. RESTORE SESSION DATA ---
            try {
                const earlier = JSON.parse(sessionStorage.getItem(key('partA')) || 'null');
                if (earlier) {
                    page = 2;
                    updateWizardUI();

                    $('[name="target_pick"]').val(earlier.target).trigger('change');
                    $('[name="bagian"]').val(earlier.bagian);

                    $(`input[name="attendance"][value="${earlier.attendance}"]`).prop('checked', true).trigger(
                        'change');

                    if (earlier.attendance === '0') {
                        $(`input[name="ada_pengganti"][value="${earlier.has_replacement === true || earlier.has_replacement === '1' ? '1' : '0'}"]`)
                            .prop('checked', true).trigger('change');
                    }

                    if (earlier.nama_pengganti) $('[name="nama_pengganti"]').val(earlier.nama_pengganti).trigger(
                        'change');
                    if (earlier.bagian_pengganti) $('input[name="bagian_pengganti"]').val(earlier.bagian_pengganti);
                    if (earlier.kondisi_pengganti) $(
                        `input[name="kondisi_pengganti"][value="${earlier.kondisi_pengganti}"]`).prop('checked',
                        true);
                    if (earlier.kondisi) $(`input[name="kondisi"][value="${earlier.kondisi}"]`).prop('checked',
                        true);
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
