@extends('layouts.app')

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-ui-checks me-2 fs-5"></i>
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
    <div class="container-fluid max-w-800 mx-auto mb-5 pb-5">

        {{-- HEADER INFO --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="badge bg-primary fs-6 px-3 py-2 shadow-sm rounded-pill">
                <i class="bi bi-file-earmark-check me-1"></i> Bagian B
            </div>

            <div class="badge bg-white text-dark border shadow-sm px-3 py-2 rounded-pill fs-6">
                <i class="bi bi-stopwatch text-danger me-1"></i> Timer:
                <span id="stopwatch" class="text-danger fw-bold ms-1" data-start="{{ $startedAtMs }}"
                    style="display: inline-block; min-width: 55px; text-align: center; font-variant-numeric: tabular-nums;">00:00</span>
            </div>
        </div>

        {{-- PROGRESS WIZARD --}}
        <div class="progress mb-2" style="height: 6px;">
            <div class="progress-bar bg-success transition-all" id="wizard-progress" role="progressbar" style="width: 0%;">
            </div>
        </div>

        {{-- FORM START --}}
        <form id="formB" method="POST" action="{{ route('checksheets.store', ['type' => $phase]) }}"
            class="position-relative">
            @csrf

            {{-- HIDDEN DATA DARI PART A --}}
            <input type="hidden" name="schedule_plan_id" value="{{ $plan->id }}">
            <input type="hidden" name="part_a[shift]">
            <input type="hidden" name="part_a[target]">
            <input type="hidden" name="part_a[division]">
            <input type="hidden" name="part_a[attendance]">
            <input type="hidden" name="part_a[kondisi]">
            <input type="hidden" name="part_a[has_replacement]">
            <input type="hidden" name="part_a[nama_pengganti]">
            <input type="hidden" name="part_a[bagian_pengganti]">
            <input type="hidden" name="part_a[kondisi_pengganti]">

            {{-- LOOPING PERTANYAAN --}}
            @foreach ($questions as $i => $q)
                @php $idx = $i + 1; @endphp
                <div id="q_{{ $idx }}"
                    class="card border-0 shadow-sm rounded-4 mb-3 question-card animate-fade-in {{ $idx > 1 ? 'd-none' : '' }}">

                    <div
                        class="card-header bg-light border-bottom-0 py-2 rounded-top-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-primary mb-0">Pertanyaan {{ $idx }} dari {{ count($questions) }}
                        </h6>
                        @if ($q->extra_fields)
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Issue
                                Tracking</span>
                        @endif
                    </div>

                    <div class="card-body p-4">
                        {{-- Judul Pertanyaan --}}
                        <h5 class="fw-bold text-dark mb-3 lh-base">{{ $q->question_text }}</h5>

                        {{-- Opsi Jawaban --}}
                        @php
                            $choices =
                                $q->choices ?:
                                ($q->answer_type === 'b'
                                    ? ['0' => 'Tidak', '1' => 'Ya']
                                    : ['0' => 'Pilihan 0', '1' => 'Pilihan 1', '2' => 'Pilihan 2']);
                        @endphp

                        <div class="d-flex flex-column gap-2 mb-2">
                            @foreach ($choices as $val => $label)
                                @php
                                    $v = (string) $val;
                                    $lbl = is_array($label) ? $label['label'] ?? json_encode($label) : $label;

                                    // Kasih warna beda buat opsi terakhir (biasanya "Terburuk" / NG)
                                    $isLastOption = $loop->last;
                                    $outlineColor = $isLastOption ? 'outline-success' : 'outline-primary';
                                @endphp

                                <div>
                                    <input class="btn-check answer-radio" type="radio" name="answers[{{ $q->id }}]"
                                        id="q{{ $q->id }}_{{ $v }}" value="{{ $v }}"
                                        data-extra="{{ $q->extra_fields }}" data-qid="{{ $q->id }}">
                                    <label class="btn btn-{{ $outlineColor }} w-100 py-3 fw-bold text-start ps-4 rounded-3"
                                        for="q{{ $q->id }}_{{ $v }}">
                                        <span class="me-2 d-inline-block text-center" style="width: 24px;">
                                            {{ chr(65 + $loop->index) }}. </span>
                                        {{ $lbl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Extra Fields (Muncul jika extra_fields = 1 & opsi yg dipilih bukan yg terakhir) --}}
                        <div
                            class="extra-fields-wrap mt-3 p-3 bg-danger-subtle border border-danger-subtle rounded-3 d-none animate-fade-in">
                            <div class="mb-3 problem-wrap">
                                <label class="form-label fw-bold text-danger small text-uppercase"><i
                                        class="bi bi-x-circle me-1"></i>Problem</label>
                                <input type="text" name="problems[{{ $q->id }}]"
                                    class="form-control border-danger" placeholder="Ketik alasan / temuan...">
                            </div>
                            <div class="counter-wrap">
                                <label class="form-label fw-bold text-primary small text-uppercase"><i
                                        class="bi bi-wrench me-1"></i>Countermeasure</label>
                                <input type="text" name="countermeasures[{{ $q->id }}]"
                                    class="form-control border-primary" placeholder="Ketik perbaikan...">
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

        </form>
    </div>

    {{-- STICKY ACTION BAR --}}
    <div class="action-bar d-flex justify-content-between align-items-center px-3 px-md-5 bg-white border-top shadow-lg"
        style="z-index: 1030;">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" id="prevQ"
            style="visibility: hidden;">
            <i class="bi bi-arrow-left me-2"></i> Back
        </button>
        <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-bold" id="cancelBtn"
            data-bs-toggle="modal" data-bs-target="#cancelModal">
            <i class="bi bi-x-lg me-2"></i> Batal
        </button>

        <div class="fw-bold text-muted small px-3 py-1 bg-light rounded-pill border" id="pageInfo">
            {{ count($questions) ? '1 / ' . count($questions) : '0 / 0' }}
        </div>

        <div>
            <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm" id="nextQ">
                Next <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="button" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow-sm d-none" id="submitBtn">
                <i class="bi bi-send-check me-2"></i> Submit Data
            </button>
        </div>
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
            const key = (k) => `cl:plan:${PLAN}:phase:${PHASE}:${k}`;
            const totalQuestions = {{ count($questions) }};
            const DASHBOARD_URL = @json(route('dashboard'));

            // --- 1. TIMER MURNI DARI BLADE ---
            const started = parseInt($('#stopwatch').data('start'), 10);
            setInterval(() => {
                const sec = Math.floor((Date.now() - started) / 1000);
                const mm = String(Math.floor(sec / 60)).padStart(2, '0');
                const ss = String(sec % 60).padStart(2, '0');
                $('#stopwatch').text(`${mm}:${ss}`);
            }, 1000);

            // --- 2. INJECT PART A DATA ---
            try {
                const a = JSON.parse(sessionStorage.getItem(key('partA')) || 'null');
                if (a) {
                    $('[name="part_a[shift]"]').val(a.shift);
                    $('[name="part_a[target]"]').val(a.target);
                    $('[name="part_a[division]"]').val(a.bagian);
                    $('[name="part_a[attendance]"]').val(a.attendance);
                    $('[name="part_a[kondisi]"]').val(a.kondisi);
                    $('[name="part_a[has_replacement]"]').val(a.has_replacement || 0);
                    $('[name="part_a[nama_pengganti]"]').val(a.nama_pengganti);
                    $('[name="part_a[bagian_pengganti]"]').val(a.bagian_pengganti);
                    $('[name="part_a[kondisi_pengganti]"]').val(a.kondisi_pengganti);
                }
            } catch (e) {
                console.error('Gagal mengambil data Part A', e);
            }

            // --- Sisa script Wizard dan Validasi tetep sama ---
            let cur = 1;
            const updateUI = () => {
                $('#pageInfo').text(`${cur} / ${totalQuestions}`);
                const percent = ((cur - 1) / totalQuestions) * 100;
                $('#wizard-progress').css('width', cur === totalQuestions ? '100%' : `${percent}%`);
                $('#prevQ').css('visibility', cur === 1 ? 'hidden' : 'visible');

                if (cur === totalQuestions) {
                    $('#nextQ').addClass('d-none');
                    $('#submitBtn').removeClass('d-none');
                } else {
                    $('#nextQ').removeClass('d-none');
                    $('#submitBtn').addClass('d-none');
                }
            };

            const go = (n) => {
                if (n < 1 || n > totalQuestions) return;
                $(`#q_${cur}`).addClass('d-none');
                cur = n;
                $(`#q_${cur}`).removeClass('d-none');
                updateUI();
            };
            updateUI();

            const showToast = (message) => {
                const toastHtml = `
                <div class="toast align-items-center text-bg-warning border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body fw-bold">${message}</div>
                        <button type="button" class="btn-close btn-close-black me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;

                let $container = $('.toast-container');
                if ($container.length === 0) {
                    $container = $(
                        '<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1060;"></div>'
                    );
                    $('body').append($container);
                }

                const $toast = $(toastHtml).appendTo($container);
                const toastInstance = new bootstrap.Toast($toast[0], {
                    delay: 3000
                });
                toastInstance.show();
                $toast.on('hidden.bs.toast', () => $toast.remove());
            };

            const validateCurrentQuestion = () => {
                const $card = $(`#q_${cur}`);
                const isAnswered = $card.find('.answer-radio').is(':checked');
                const $extraWrap = $card.find('.extra-fields-wrap');

                if (!isAnswered) {
                    showToast('Pilih salah satu jawaban dulu bos!');
                    return false;
                }

                if ($extraWrap.hasClass('d-none') === false) {
                    const probVal = $extraWrap.find('input[name^="problems"]').val().trim();
                    const countVal = $extraWrap.find('input[name^="countermeasures"]').val().trim();

                    if (probVal === '') {
                        showToast('Kolom Problem harus diisi!');
                        $extraWrap.find('input[name^="problems"]').focus();
                        return false;
                    }
                    if (countVal === '') {
                        showToast('Kolom Tindakan (Countermeasure) harus diisi!');
                        $extraWrap.find('input[name^="countermeasures"]').focus();
                        return false;
                    }
                }
                return true;
            };

            $('#prevQ').on('click', () => go(cur - 1));
            $('#nextQ').on('click', () => {
                if (validateCurrentQuestion()) go(cur + 1);
            });

            $('.answer-radio').on('change', function() {
                const extra = parseInt($(this).data('extra'), 10);
                const val = $(this).val();
                const $card = $(this).closest('.card-body');
                const $wrap = $card.find('.extra-fields-wrap');

                const radios = $card.find('.answer-radio').toArray();
                const lastRadioVal = radios[radios.length - 1].value;
                const isNotLastChoice = (val !== lastRadioVal);

                if (extra === 1 && isNotLastChoice) {
                    $wrap.removeClass('d-none');
                } else {
                    $wrap.addClass('d-none');
                    $wrap.find('input').val('');
                }
            });

            $('#submitBtn').on('click', async function(e) {
                e.preventDefault();
                if (!validateCurrentQuestion()) return;

                const $btn = $(this);
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                const form = document.getElementById('formB');
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (response.ok) {
                        sessionStorage.removeItem(key('partA'));
                        window.location.href = DASHBOARD_URL;
                    } else {
                        const err = await response.json();
                        console.error('Validation Errors:', err);
                        alert('Gagal menyimpan: Error validasi data dari server.');
                        $btn.prop('disabled', false).html(
                            '<i class="bi bi-send-check me-2"></i> Submit Data');
                    }
                } catch (err) {
                    alert('Terjadi kesalahan koneksi.');
                    $btn.prop('disabled', false).html(
                        '<i class="bi bi-send-check me-2"></i> Submit Data');
                }
            });

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
        });
    </script>
@endsection
