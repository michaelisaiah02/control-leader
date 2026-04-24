@extends('layouts.app')

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-bullseye me-2 fs-6"></i>
        <span class="fs-6 fw-bold text-uppercase text-truncate">Setting Target</span>
    </div>
@endpush

@section('content')
    <div class="container-fluid dashboard-container pb-2 pb-lg-3 pb-xxl-4 my-2">

        <div class="card border-0 shadow-sm rounded-4 animate-fade-in">
            <div
                class="card-header bg-light border-bottom-0 py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-secondary mb-0 text-uppercase small">
                    <i class="bi bi-gear-fill me-1"></i> Target Configuration
                </h6>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-primary text-secondary small text-uppercase">
                            <tr>
                                <th scope="col" class="text-center py-3" style="width: 50px;">No</th>
                                <th scope="col" class="py-3">Tipe Checksheet</th>
                                <th scope="col" class="text-center py-3" style="width: 150px;">Target Score</th>
                                <th scope="col" class="text-center py-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach ($targets as $index => $target)
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-bold">
                                        {{ Str::title(str_replace('_', ' ', $target->report)) }}
                                    </td>
                                    <td class="text-center">
                                        {{-- Kasih class target-badge dan penanda data-report --}}
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fs-6 rounded-pill target-badge"
                                            data-report="{{ $target->report }}">
                                            {{ str_replace('.', ',', (float) $target->value) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold btn-edit"
                                            data-report="{{ $target->report }}" data-val="{{ (float) $target->value }}">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ACTION BAR (Footer) --}}
        <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-start align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- MODAL EDIT TARGET --}}
    <div class="modal fade" id="targetModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content rounded-4 border-0 shadow-lg" method="POST" id="targetForm"
                action="{{ route('targets.update') }}">
                @csrf
                <input type="hidden" name="report" id="target_report">

                <div class="modal-header border-bottom-0 pb-0 mt-2">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-sliders me-2"></i>Edit Target Score
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="report_label" class="form-label small fw-bold text-secondary text-uppercase mb-1">Jenis
                            Report</label>
                        <input type="text" class="form-control bg-light text-muted fw-bold" id="report_label" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="value" class="form-label small fw-bold text-secondary text-uppercase mb-1">Target
                            Value</label>
                        <div class="input-group">
                            <input type="number" class="form-control fw-bold fs-5 text-primary" id="value"
                                name="value" placeholder="100" min="0" max="100" required>
                            <span class="input-group-text bg-white fw-bold text-secondary">%</span>
                        </div>
                        <div class="form-text small"><i class="bi bi-info-circle me-1"></i>Masukkan angka antara 0 - 100.
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 pb-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btn-save">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {

            // Fungsi Toast Helper
            function showToast(type, message) {
                let icon = 'bi-info-circle-fill';
                if (type === 'danger') icon = 'bi-x-circle-fill';
                if (type === 'warning') icon = 'bi-exclamation-triangle-fill';
                if (type === 'success') icon = 'bi-check-circle-fill';

                const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body fw-bold text-white">
                            <i class="bi ${icon} me-2"></i> ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;

                let $container = $('.toast-container.js-toast-wrap');
                if ($container.length === 0) {
                    $container = $(
                        '<div class="toast-container js-toast-wrap position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1060;"></div>'
                    );
                    $('body').append($container);
                }

                const $toast = $(toastHtml).appendTo($container);
                const toastInstance = new bootstrap.Toast($toast[0], {
                    delay: 3000
                });
                toastInstance.show();
                $toast.on('hidden.bs.toast', () => $toast.remove());
            }

            // Saat tombol Edit diklik
            $('.btn-edit').on('click', function() {
                const id = $(this).data('id');
                const report = $(this).data('report');
                const val = $(this).data('val');

                // Mapping report name buat ditampilin di UI biar keren
                let reportName = report;
                if (report === 'consistency_supervisor') reportName = 'Consistency Supervisor';
                if (report === 'consistency_leader') reportName = 'Consistency Leader';
                if (report === 'score_supervisor') reportName = 'Score Supervisor';
                if (report === 'score_leader') reportName = 'Score Leader';
                if (report === 'score_operator') reportName = 'Score Operator';

                // Masukin data ke form modal
                $('#target_report').val(report);
                $('#report_label').val(reportName);
                $('#value').val(val);

                // Tampilkan Modal
                bootstrap.Modal.getOrCreateInstance(document.getElementById('targetModal')).show();
            });

            // Saat form disubmit
            $('#targetForm').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const url = form.attr('action');
                const data = form.serialize();
                const btnSave = $('#btn-save');
                const originalText = btnSave.html();

                // Tangkap nilai report dan angka baru dari form sebelum disubmit
                const reportKey = $('#target_report').val();
                const newValue = $('#value').val();

                btnSave.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                $.post(url, data)
                    .done(function(response) {
                        // Tutup Modal
                        bootstrap.Modal.getInstance(document.getElementById('targetModal')).hide();

                        // 🔥 MAGIC DOM MANIPULATION 🔥
                        // 1. Format angka baru (Hilangin nol ga penting & ganti titik jadi koma)
                        let formattedVal = parseFloat(newValue).toString().replace('.', ',');

                        // 2. Tembak langsung teks barunya ke layar (tanpa refresh)
                        $(`.target-badge[data-report="${reportKey}"]`).text(`${formattedVal}%`);

                        // 3. Update data di tombol Edit biar kalo diklik lagi angkanya update
                        $(`.btn-edit[data-report="${reportKey}"]`).data('val', parseFloat(newValue))
                            .attr('data-val', parseFloat(newValue));

                        // 4. Balikin tombol dan kasih Toast sukses
                        btnSave.prop('disabled', false).html(originalText);
                        showToast('success', 'Target berhasil diperbarui!');
                    })
                    .fail(function(xhr) {
                        console.error('Update Target Error:', xhr.responseText);
                        showToast('danger', 'Gagal memperbarui target!');
                        btnSave.prop('disabled', false).html(originalText);
                    });
            });

        });
    </script>
@endsection
