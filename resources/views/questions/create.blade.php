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
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-plus-circle me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase">Add Question</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid layout-fixed pb-2">
        <form method="POST" action="{{ route('question.store') }}" id="questionForm" class="h-100 d-flex flex-column">
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
                        </div>
                    </div>
                </div>

                {{-- RIGHT: TOOLS --}}
                <div class="col-12 col-md-4 h-100">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3 d-flex flex-column">
                            <h6 class="fw-bold text-secondary mb-3 small text-uppercase">Config</h6>

                            <div class="d-grid gap-2 mb-auto">
                                {{-- TOGGLE EXTRA FIELDS YANG LEBIH SIMPEL --}}
                                <div class="form-check form-switch border rounded-3 p-3 d-flex align-items-center justify-content-between mb-3 bg-light cursor-pointer"
                                    id="extraFieldsContainer">
                                    <label class="form-check-label fw-bold mb-0 cursor-pointer" for="extra_fields">
                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Wajib Isi Problem &
                                        Countermeasure?
                                    </label>
                                    <input class="form-check-input fs-5 m-0" type="checkbox" role="switch"
                                        id="extra_fields" name="extra_fields" value="1">
                                </div>

                                <div class="alert alert-info border small text-muted mb-3">
                                    <i class="bi bi-info-circle me-1"></i> Aktifkan jika opsi jawaban NG mewajibkan user
                                    mengisi form Issue Tracking.
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold shadow-sm" id="btnSubmit">
                                    <i class="bi bi-save me-2"></i> Save Question
                                </button>
                                <a href="{{ route('question.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
        function toggleOptionMode() {
            // Cari value radio button yang lagi checked
            let isMode3 = $('input[name="option_mode"]:checked').val() === '3';

            // Pisahin toggle-nya biar ga tabrakan
            $('#option-3-container')
                .toggleClass('d-flex', isMode3) // Tambah d-flex kalau mode 3, hapus kalau nggak
                .toggleClass('d-none', !isMode3); // Tambah d-none kalau BUKAN mode 3, hapus kalau iya

            // Disable inputnya biar ga ikut ke-submit pas mode 2
            $('#input-3').prop({
                disabled: !isMode3,
                required: isMode3
            });
        }

        $(document).ready(function() {
            // 1. Logic 2 vs 3 Opsi Pake jQuery (Jauh lebih pendek kan?)
            $('input[name="option_mode"]').change(toggleOptionMode);
            toggleOptionMode();

            // 2. Efek Visual UI pas switch Problem di-klik
            $('#extra_fields').change(function() {
                if ($(this).is(':checked')) {
                    $('#extraFieldsContainer').removeClass('bg-light').addClass(
                        'bg-primary-subtle border-primary');
                } else {
                    $('#extraFieldsContainer').removeClass('bg-primary-subtle border-primary').addClass(
                        'bg-light');
                }
            });

            // 3. SortableJS (Tetep jalan secara native karena performance lebih bagus buat drag & drop)
            Sortable.create(document.getElementById("radio-options-list"), {
                animation: 150,
                handle: ".cursor-grab",
                ghostClass: "sortable-ghost",
                onStart: () => $('body').addClass('cursor-grabbing'),
                onEnd: () => $('body').removeClass('cursor-grabbing')
            });

            // 4. Loading State Form Submit
            $('#questionForm').submit(function() {
                if (this.checkValidity()) {
                    $('#btnSubmit').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
                }
            });
        });
    </script>
@endsection
