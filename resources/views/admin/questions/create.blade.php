@extends('layouts.app')

@section('styles')
    <style>
        .builder-card {
            transition: all 0.3s ease;
            border: 1px solid var(--bs-gray-200);
        }

        .builder-card:hover {
            border-color: var(--bs-primary);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }

        .cursor-grab {
            cursor: grab;
            color: #adb5bd;
        }

        .cursor-grab:hover {
            color: var(--bs-primary);
        }

        .sortable-ghost {
            opacity: 0.4;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
        }

        /* Style buat Toggle 2 vs 3 */
        .option-mode-selector .btn-check:checked+.btn {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
            box-shadow: 0 4px 6px rgba(var(--bs-primary-rgb), 0.3);
        }

        .option-mode-selector .btn {
            border: 1px solid #dee2e6;
            color: var(--bs-gray-600);
            font-weight: 600;
        }
    </style>
@endsection

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-3 py-1 mt-1 mb-0 rounded-3 bg-white bg-opacity-10 border border-light text-white subtitle">
        <i class="bi bi-plus-circle me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase">Add Question</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid layout-fixed pb-2">
        <form method="POST" action="{{ route('admin.question.store') }}" id="questionForm" class="h-100 d-flex flex-column">
            @csrf

            {{-- SECTION 1: MAIN INFO --}}
            <div class="card border-0 shadow-sm mb-2 rounded-3 shrink-0">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Question Text</label>
                            <input id="question_text" type="text" name="question_text" class="form-control fw-bold"
                                placeholder="e.g., Apakah APD lengkap?" required autofocus>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Category Type</label>
                            <select name="package" id="package" class="form-select" required>
                                <option value="" selected disabled>Select Type...</option>
                                <option value="awal_shift">Awal Shift</option>
                                <option value="saat_bekerja">Saat Bekerja</option>
                                <option value="setelah_istirahat">Setelah Istirahat</option>
                                <option value="akhir_shift">Akhir Shift</option>
                                <option value="leader">Leader</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: BUILDER AREA --}}
            <div class="row g-2 grow overflow-hidden">

                {{-- LEFT: FORM BUILDER --}}
                <div class="col-12 col-md-8 h-100 d-flex flex-column">
                    <div class="card border-0 shadow-sm rounded-3 h-100 bg-light">
                        <div
                            class="card-header bg-white border-bottom-0 py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-ui-checks me-2"></i>Answer Options</h6>
                            <span class="badge bg-light text-secondary border">Urutkan: Terbaik <i
                                    class="bi bi-hand-thumbs-up-fill text-success"></i> ke Terburuk <i
                                    class="bi bi-hand-thumbs-down-fill text-danger"></i></span>
                        </div>

                        <div class="card-body overflow-y-auto p-3">

                            <div class="builder-card bg-white p-3 rounded-3 mb-3">

                                {{-- MODE SELECTOR (2 vs 3) --}}
                                <div class="text-center mb-3">
                                    <label class="small text-uppercase fw-bold text-muted mb-1 d-block">Jumlah Pilihan
                                        Jawaban</label>
                                    <div class="btn-group option-mode-selector" role="group">
                                        <input type="radio" class="btn-check" name="option_mode" id="mode2"
                                            value="2" checked>
                                        <label class="btn py-1" for="mode2">2 Opsi (A/B)</label>

                                        <input type="radio" class="btn-check" name="option_mode" id="mode3"
                                            value="3">
                                        <label class="btn py-1" for="mode3">3 Opsi (A/B/C)</label>
                                    </div>
                                </div>

                                <hr class="opacity-10 mt-2 mb-3">

                                {{-- INPUT LIST (Sortable) --}}
                                <div id="radio-options-list" class="d-flex flex-column gap-3">
                                    {{-- Opsi 1 --}}
                                    <div class="input-group option-item">
                                        <span class="input-group-text bg-white border-end-0 cursor-grab ps-3"><i
                                                class="bi bi-grip-vertical"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 bg-light"
                                            name="choices[]" value="OK" placeholder="Jawaban Terbaik" required>
                                    </div>

                                    {{-- Opsi 2 --}}
                                    <div class="input-group option-item">
                                        <span class="input-group-text bg-white border-end-0 cursor-grab ps-3"><i
                                                class="bi bi-grip-vertical"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 bg-light"
                                            name="choices[]" value="NG" placeholder="Jawaban Terburuk" required>
                                    </div>

                                    {{-- Opsi 3 (Hidden by default) --}}
                                    <div class="input-group option-item d-none" id="option-3-container">
                                        <span class="input-group-text bg-white border-end-0 cursor-grab ps-3"><i
                                                class="bi bi-grip-vertical"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 bg-light"
                                            id="input-3" name="choices[]" value="Repair" placeholder="Opsi Tambahan"
                                            disabled>
                                    </div>
                                </div>

                            </div>

                            {{-- EXTRA FIELDS BLOCK --}}
                            <div id="extra-fields-block" class="builder-card bg-white p-3 rounded-3 mb-3 animate-fade-in"
                                style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="fw-bold text-dark small text-uppercase"><i
                                            class="bi bi-exclamation-triangle me-2"></i>Issue Tracking</label>
                                    <button type="button" class="btn-close btn-sm" id="btnRemoveExtra"></button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" name="problem_label"
                                                class="form-control form-control-sm" placeholder="Label">
                                            <label>Label Problem</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" name="countermeasure_label"
                                                class="form-control form-control-sm" placeholder="Label">
                                            <label>Label Countermeasure</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- RIGHT: TOOLS --}}
                <div class="col-12 col-md-4 h-100">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3 d-flex flex-column">
                            <h6 class="fw-bold text-secondary mb-3 small text-uppercase">Config</h6>

                            <div class="d-grid gap-2 mb-auto">
                                <div class="alert alert-light border small text-muted mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Pilih mode <strong>2 Opsi</strong> atau <strong>3 Opsi</strong> di panel kiri.
                                </div>

                                <button type="button" class="btn btn-outline-secondary text-start py-3 border"
                                    id="btnToggleExtra">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <span><i class="bi bi-input-cursor-text me-2"></i> Problem & Countermeasure</span>
                                        <i class="bi bi-toggle-off fs-5" id="toggleIcon"></i>
                                    </div>
                                </button>
                            </div>

                            <hr class="text-muted opacity-25">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold shadow-sm">
                                    <i class="bi bi-save me-2"></i> Save Question
                                </button>
                                <a href="{{ route('admin.question.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script type="module">
        document.addEventListener("DOMContentLoaded", () => {
            const radioList = document.getElementById("radio-options-list");
            const extraBlock = document.getElementById("extra-fields-block");
            const btnToggleExtra = document.getElementById("btnToggleExtra");
            const toggleIcon = document.getElementById("toggleIcon");

            // --- 1. LOGIC 2 vs 3 OPSI (SWITCHER) ---
            const modeRadios = document.querySelectorAll('input[name="option_mode"]');
            const container3 = document.getElementById('option-3-container');
            const input3 = document.getElementById('input-3');

            // Function buat update tampilan berdasarkan mode
            function updateOptionMode() {
                // Cari mana yang diceklis
                const selectedMode = document.querySelector('input[name="option_mode"]:checked').value;
                const optionItems = radioList.querySelectorAll('.option-item');

                if (selectedMode === "3") {
                    // Show item ke-3 (tapi harus tau item ke-3 itu elemen yang mana kalau udah disortir)
                    // Simplenya: Kita toggle class d-none di container ke-3 yang kita hardcode ID-nya
                    container3.classList.remove('d-none');
                    container3.classList.add('d-flex');
                    input3.disabled = false; // Aktifin biar dikirim ke server
                    input3.required = true;
                } else {
                    // Hide item ke-3
                    container3.classList.add('d-none');
                    container3.classList.remove('d-flex');
                    input3.disabled = true; // Disable biar GAK dikirim ke server
                    input3.required = false;
                }
            }

            // Listen perubahan switch
            modeRadios.forEach(radio => {
                radio.addEventListener('change', updateOptionMode);
            });


            // --- 2. SORTABLE (Tetap ada buat nuker posisi OK/NG) ---
            Sortable.create(radioList, {
                animation: 150,
                handle: ".cursor-grab",
                ghostClass: "sortable-ghost",
                onStart: () => document.body.classList.add('cursor-grabbing'),
                onEnd: () => {
                    document.body.classList.remove('cursor-grabbing');
                    // Kalau mau re-numbering (1. 2. 3.) secara visual bisa disini
                }
            });


            // --- 3. TOGGLE EXTRA FIELDS (Sama kayak sebelumnya) ---
            function setExtraState(isActive) {
                const inputs = extraBlock.querySelectorAll('input');
                if (isActive) {
                    extraBlock.style.display = 'block';
                    btnToggleExtra.classList.add('border-primary', 'bg-primary-subtle');
                    btnToggleExtra.classList.remove('btn-outline-secondary');
                    toggleIcon.classList.replace('bi-toggle-off', 'bi-toggle-on');
                    toggleIcon.classList.add('text-primary');
                    inputs.forEach(el => el.required = true);
                } else {
                    extraBlock.style.display = 'none';
                    btnToggleExtra.classList.remove('border-primary', 'bg-primary-subtle');
                    btnToggleExtra.classList.add('btn-outline-secondary');
                    toggleIcon.classList.replace('bi-toggle-on', 'bi-toggle-off');
                    toggleIcon.classList.remove('text-primary');
                    inputs.forEach(el => {
                        el.required = false;
                        el.value = '';
                    });
                }
            }

            btnToggleExtra.addEventListener("click", () => {
                const isHidden = extraBlock.style.display === 'none';
                setExtraState(isHidden);
            });

            document.getElementById("btnRemoveExtra").addEventListener("click", () => {
                setExtraState(false);
            });

            // Loading State
            document.getElementById('questionForm').addEventListener('submit', function() {
                if (this.checkValidity()) {
                    const btn = this.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                }
            });
        });
    </script>
@endsection
