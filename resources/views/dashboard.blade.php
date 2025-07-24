@extends('layouts.app')

@section('styles')
    <style>
        #title {
            opacity: 0;
            transform: translateY(0);
            transition: opacity 0.1s ease, transform 0.2s ease;
        }

        #title.show-title {
            opacity: 1;
            transform: translateY(-20px);
        }

        /* kosongkan animation-name default, nanti kita isi via JS */
        #warning-text {
            display: inline-block;
            white-space: nowrap;
            will-change: transform;
        }

        #danger-text {
            display: inline-block;
            white-space: nowrap;
            will-change: transform;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid overflow-hidden m-0">
        <h5 class="text-center text-primary position-relative m-0" id="warning">
            <span class="bg-warning rounded-3 p-2 m-0" id="warning-text">&nbsp;</span>
        </h5>
    </div>
    <div class="container-fluid overflow-hidden m-0">
        <h5 class="text-center text-primary position-relative m-0" id="danger">
            <span class="bg-danger rounded-3 p-2 m-0" id="danger-text">&nbsp;</span>
        </h5>
    </div>
    <div class="container" id="dashboard">
        <div class="row justify-content-md-around justify-content-center mb-3">
            <div class="col-md-5 main-menu">
                <div class="card border-0">
                    <img src="{{ asset('icon/input.svg') }}" class="mx-auto mt-1 icon menu-btn" alt="Input"
                        data-target="menu-kalibrasi" id="img-input">
                    <div class="card-body mx-auto" id="btn-kalibrasi">
                        <button class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1"
                            data-target="menu-kalibrasi">KALIBRASI</button>
                    </div>
                    <div class="menu-kalibrasi row d-none mx-md-0 card-body mx-auto" id="btn-input">
                        <button class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1"
                            data-target="menu-input">INPUT</button>
                    </div>
                    <div class="menu-input menu-section row d-none mx-1 mx-md-0">
                        <a class="btn btn-primary py-2 my-3 rounded-4 btn1" href="{{ route('input.new.equipment') }}">NEW
                            EQUIPMENT</a>
                        <a class="btn btn-primary py-2 rounded-4 btn1" href="{{ route('input.repair') }}">REPAIR
                            DATA</a>
                    </div>
                </div>
            </div>
            <div class="col-md-5 main-menu">
                <div class="card border-0">
                    <img src="{{ asset('icon/report.svg') }}" class="mx-auto mt-1 icon menu-btn" alt="Report"
                        data-target="menu-control" id="img-report">
                    <div class="card-body mx-auto" id="btn-control">
                        <button class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1 text-nowrap"
                            data-target="menu-control">CONTROL
                            LEADER</button>
                    </div>
                    <div class="menu-kalibrasi row d-none mx-md-0 card-body mx-auto" id="btn-report">
                        <button class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1"
                            data-target="menu-report">REPORT</button>
                    </div>
                    <div class="menu-input menu-section row d-none mx-1 mx-md-0">
                        <a class="btn btn-primary py-2 my-3 rounded-4 btn1"
                            href="{{ route('input.calibration.data') }}">CALIBRATION DATA</a>
                        @if (auth()->user()->role === 'admin')
                            <button class="btn btn-primary py-2 rounded-4 menu-btn btn1" data-target="master-data">MASTER
                                DATA</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-5 d-grid row-gap-1 row-gap-md-3 text-center d-none master-data mb-1 mb-md-0">
                <a class="btn btn-primary py-4 rounded-4 menu-btn btn2 align-content-center"
                    href="{{ route('admin.users.index') }}">Users</a>
                <a class="btn btn-primary py-4 rounded-4 menu-btn btn2 align-content-center"
                    href="{{ route('admin.equipments.index') }}">Equipments</a>
            </div>
            <div class="col-md-5 d-grid row-gap-1 row-gap-md-3 text-center d-none master-data">
                <a class="btn btn-primary py-4 rounded-4 menu-btn btn2 align-content-center"
                    href="{{ route('admin.units.index') }}">Units</a>
                <a href="{{ route('admin.master-lists.index') }}"
                    class="btn btn-primary py-4 rounded-4 menu-btn btn2 align-content-center">Master Lists</a>
                <a class="btn btn-primary py-4 rounded-4 menu-btn btn2 align-content-center"
                    href="{{ route('admin.standards.index') }}">Acceptance Criteria</a>
            </div>
        </div>
        <form id="btn-logout" action="{{ route('logout') }}" method="post" class="d-flex justify-content-center">
            @csrf
            <button type="submit" class="btn btn-primary text-center rounded-3 px-5 btn2">LOG
                OUT</button>
        </form>
        <div id="btn-back" class="row justify-content-end d-none">
            <div class="col-auto">
                <button type="button" class="btn btn-primary text-decoration-underline shadow-lg menu-btn px-5 btn2"
                    id="back" data-target="back">Back</button>
            </div>
        </div>
    </div>
    @if ($pending)
        <!-- Modal tidak bisa ditutup -->
        <div class="modal fade" id="acceptanceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="acceptanceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content needs-validation" method="POST" id="standardForm"
                    action="{{ route('standards.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="master_list_id" value="{{ $pending->master_list_id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="standardModalLabel">Add Standard</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="id-num" name="id_num" hidden
                                value="{{ $masterList->id_num }}">
                            <input class="form-control"
                                value="{{ $masterList->id_num }} - {{ $masterList->sn_num }} - {{ $masterList->equipment->name }}"
                                disabled>
                            <label for="id-num" class="form-label">ID Number</label>
                        </div>
                        <div class="mb-3">
                            <div class="invalid-feedback">ID Number is required.</div>
                        </div>
                        <div class="input-group input-group-sm mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control form-control-sm" id="param-01"
                                    placeholder="Parameter 1" name="param_01" step="0.01" min="0.01" required>
                                <label for="param-01">Parameter 1</label>
                            </div>
                            <div class="form-floating">
                                <input type="number" class="form-control form-control-sm" id="param-02"
                                    placeholder="Parameter 2" name="param_02" step="0.01" min="0.01" required>
                                <label for="param-02">Parameter 2</label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-03" placeholder="Parameter 3"
                                    name="param_03" step="0.01" min="0.01" required>
                                <label for="param-03">Parameter 3</label>
                            </div>
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-04" placeholder="Parameter 4"
                                    name="param_04" step="0.01" min="0.01" required>
                                <label for="param-04">Parameter 4</label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-05" placeholder="Parameter 5"
                                    name="param_05" step="0.01" min="0.01" required>
                                <label for="param-05">Parameter 5</label>
                            </div>
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-06" placeholder="Parameter 6"
                                    name="param_06" step="0.01" min="0.01" required>
                                <label for="param-06">Parameter 6</label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-07" placeholder="Parameter 7"
                                    name="param_07" step="0.01" min="0.01" required>
                                <label for="param-07">Parameter 7</label>
                            </div>
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-08" placeholder="Parameter 8"
                                    name="param_08" step="0.01" min="0.01" required>
                                <label for="param-08">Parameter 8</label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-09" placeholder="Parameter 9"
                                    name="param_09" step="0.01" min="0.01" required>
                                <label for="param-09">Parameter 9</label>
                            </div>
                            <div class="form-floating">
                                <input type="number" class="form-control" id="param-10" placeholder="Parameter 10"
                                    name="param_10" step="0.01" min="0.01" required>
                                <label for="param-10">Parameter 10</label>
                            </div>
                        </div>
                        <span class="text-danger">Note: If Parameter no need, please fill 99999</span>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <script type="module">
            // Matikan backdrop & ESC
            const modalEl = document.getElementById('acceptanceModal');
            const modalInstance = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false,
            });
            modalEl.addEventListener('shown.bs.modal', () => {
                modalEl.querySelector('input').focus();
            });

            // Tampilkan modal saat halaman dimuat
            document.addEventListener('DOMContentLoaded', () => {
                modalInstance.show();
            });

            $('.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
        </script>
    @endif

    <div class="toast-container position-fixed bottom-0 end-0 p-3 z-3"></div>
    <x-toast />
@endsection
@section('scripts')
    <script type="module">
        $(document).ready(function() {
            $(".menu-btn").click(function(event) {
                var target = $(this).attr("data-target"); // Ambil attribute asli, bukan cache jQuery
                console.log('[Menu Button] Target:', target);
                if (target === "menu-kalibrasi") {
                    // Opacity gambar input 50%, hilangkan tombol input
                    $("#img-input").css("opacity", "1");
                    $("#img-report").css("opacity", "1");
                    setTimeout(() => {
                        $("#img-input").attr("data-target", "menu-input");
                        $("#img-report").attr("data-target", "menu-report");
                    }, 100); // ubah setelah klik selesai
                    $("#btn-kalibrasi").addClass("d-none"); // Sembunyikan tombol kalibrasi
                    $("#btn-control").addClass("d-none"); // Sembunyikan tombol control leader
                    $("#btn-input").removeClass("d-none"); // Sembunyikan tombol input
                    $("#btn-report").removeClass("d-none"); // Sembunyikan tombol report
                    $("#btn-logout").addClass("d-none");
                    $("#btn-back").removeClass("d-none"); // Tampilkan tombol back
                    $(".menu-section").addClass("d-none"); // Sembunyikan semua menu-section
                    $("#back").addClass("back-kalibrasi");
                    $("#back").removeClass("back-input");
                    $("#back").removeClass("back-master");
                    $("." + target).removeClass("d-none"); // Tampilkan yang dipilih

                    // Ubah judul
                    $("#title").text("KALIBRASI").removeClass("d-none");
                    setTimeout(() => {
                        $("#title").addClass("show-title");
                    }, 5); // kasih delay sedikit supaya transisi bisa kebaca
                } else if (target === "menu-input") {
                    // Opacity gambar input 50%, hilangkan tombol input
                    $("#img-input").css("opacity", "1");
                    $("#img-report").css("opacity", "0.5");
                    $("#btn-input").addClass("d-none"); // Sembunyikan tombol input
                    $("#btn-report").addClass("d-none"); // Sembunyikan tombol report
                    $("#btn-logout").addClass("d-none");
                    $("#btn-back").removeClass("d-none"); // Tampilkan tombol back
                    $(".menu-section").addClass("d-none"); // Sembunyikan semua menu-section
                    $("#back").removeClass("back-kalibrasi");
                    $("#back").addClass("back-input");
                    $("#back").removeClass("back-master");
                    $("." + target).removeClass("d-none"); // Tampilkan yang dipilih

                    // Ubah judul
                    $("#title").text("INPUT").removeClass("d-none");
                    setTimeout(() => {
                        $("#title").addClass("show-title");
                    }, 5); // kasih delay sedikit supaya transisi bisa kebaca
                } else if (target === "menu-report") {
                    window.location.href = "/report"
                } else if (target === "back") {
                    if ($("#back").hasClass("back-kalibrasi")) {
                        // Tutup semua menu-section
                        $("#img-input").css("opacity", "1");
                        $("#img-report").css("opacity", "1");
                        setTimeout(() => {
                            $("#img-input").attr("data-target", "menu-kalibrasi");
                            $("#img-report").attr("data-target", "menu-control");
                        }, 100); // ubah setelah klik selesai
                        $(".menu-section").addClass("d-none");
                        $("#btn-logout").removeClass("d-none");
                        $("#btn-back").addClass("d-none");
                        $("#btn-input").addClass("d-none");
                        $("#btn-report").addClass("d-none");
                        $("#btn-kalibrasi").removeClass("d-none");
                        $("#btn-control").removeClass("d-none");
                        $("#back").removeClass("back-kalibrasi");
                        // Ubah judul
                        $("#title").removeClass("show-title");
                        setTimeout(() => {
                            $("#title").html("&nbsp;");
                        }, 200); // tunggu animasi selesai (sesuai transition duration CSS)
                    }
                    if ($("#back").hasClass("back-input")) {
                        // Tutup semua menu-section
                        $("#img-input").css("opacity", "1");
                        $("#img-input").attr("data-target", "menu-input");
                        $("#img-report").css("opacity", "1");
                        $("#img-report").attr("data-target", "menu-report");
                        $(".menu-section").addClass("d-none");
                        $("#btn-logout").addClass("d-none");
                        $("#btn-back").removeClass("d-none");
                        $("#btn-input").removeClass("d-none");
                        $("#btn-report").removeClass("d-none");
                        $("#btn-kalibrasi").addClass("d-none");
                        $("#btn-control").addClass("d-none");
                        $("#back").addClass("back-kalibrasi");
                        $("#back").removeClass("back-input");
                        $("#back").removeClass("back-master");

                        // Ubah judul
                        $("#title").removeClass("show-title");
                        setTimeout(() => {
                            $("#title").html("&nbsp;");
                        }, 200); // tunggu animasi selesai (sesuai transition duration CSS)
                    }
                    if ($("#back").hasClass("back-master")) {
                        $("#img-input").css("opacity", "1");
                        $("#img-report").css("opacity", "0.5");
                        $("#btn-kalibrasi").addClass("d-none");
                        $("#btn-control").addClass("d-none");
                        $("#btn-input").addClass("d-none"); // Sembunyikan tombol input
                        $("#btn-report").addClass("d-none"); // Sembunyikan tombol report
                        $("#btn-logout").addClass("d-none");
                        $(".menu-section").removeClass("d-none"); // Sembunyikan semua menu-section
                        // Tutup semua menu-section
                        $("#dashboard").removeClass("mt-md-1");
                        $(".main-menu").removeClass("d-none");
                        $(".master-data").addClass("d-none"); // Tampilkan yang dipilih
                        $("#back").removeClass("back-kalibrasi");
                        $("#back").addClass("back-input");
                        $("#back").removeClass("back-master");

                        // Ubah judul
                        $("#title").text("INPUT").removeClass("d-none");
                        setTimeout(() => {
                            $("#title").addClass("show-title");
                        }, 5); // kasih delay sedikit supaya transisi bisa kebaca
                    }
                }
            });
        });
    </script>
    @if (auth()->user()->role === 'admin')
        <script type="module">
            $(document).ready(function() {
                $(".menu-btn").click(function() {
                    var target = $(this).data("target"); // Ambil target dari data-target button
                    if (target === "master-data") {
                        // Opacity gambar input 50%, hilangkan tombol input
                        $("#dashboard").addClass("mt-md-1");
                        $(".main-menu").addClass("d-none");
                        $("." + target).removeClass("d-none"); // Tampilkan yang dipilih
                        $("#btn-back").removeClass("d-none");
                        $("#back").removeClass("back-kalibrasi");
                        $("#back").removeClass("back-input");
                        $("#back").addClass("back-master");
                        $("#btn-logout").addClass("d-none");

                        // Ubah judul
                        $("#title").text("MASTER DATA INPUT").removeClass("d-none");
                        setTimeout(() => {
                            $("#title").addClass("show-title");
                        }, 5); // kasih delay sedikit supaya transisi bisa kebaca
                    }
                });
            })
        </script>
    @endif
    @session('success')
        <script type="module">
            $(document).ready(function() {
                $('.menu-btn[data-target="{{ session()->get('key') }}"]').trigger('click');
            })
        </script>
    @endsession
    @if (!session()->has('success'))
        @session('key')
            <script type="module">
                $(document).ready(function() {
                    $('.menu-btn[data-target="{{ session()->get('key') }}"]').trigger('click');
                })
            </script>
        @endsession
    @endif
    <script>
        // Cek apakah ada peringatan barang belum selesai kalibrasi
        document.addEventListener('DOMContentLoaded', () => {
            const warnings = @json($warnings);
            const dangers = @json($dangers);
            const speedPxPerSec = 100;

            if (!warnings.length) {
                document.getElementById('warning').classList.add('d-none');
            } else {
                document.getElementById('warning').classList.remove('d-none'); //warnings exist
            }

            if (!dangers.length) {
                document.getElementById('danger').classList.add('d-none');
            } else {
                document.getElementById('danger').classList.remove('d-none'); //dangers exist
            }

            if (warnings.length) {
                const el = document.getElementById('warning-text');
                el.textContent = warnings.join('      ');

                requestAnimationFrame(() => {
                    const textWidth = el.offsetWidth;
                    const containerWidth = el.parentElement.offsetWidth;
                    const distance = textWidth + containerWidth;
                    const durationSec = distance / speedPxPerSec;
                    const styleEl = document.createElement('style');
                    styleEl.textContent = `
            @keyframes marqueeDynamic {
                0%   { transform: translateX(${containerWidth + 100}px); }
                100% { transform: translateX(-${textWidth + 250}px); }
            }`;
                    document.head.appendChild(styleEl);

                    el.style.animationName = 'marqueeDynamic';
                    el.style.animationDuration = durationSec + 's';
                    el.style.animationTimingFunction = 'linear';
                    el.style.animationIterationCount = 'infinite';
                });
            }

            if (dangers.length) {
                const el2 = document.getElementById('danger-text');
                el2.textContent = dangers.join('      ');

                requestAnimationFrame(() => {
                    const textWidth2 = el2.offsetWidth;
                    const containerWidth2 = el2.parentElement.offsetWidth;
                    const distance2 = textWidth2 + containerWidth2;
                    const durationSec2 = distance2 / speedPxPerSec;

                    const styleEl2 = document.createElement('style');
                    styleEl2.textContent = `
            @keyframes marqueeDynamic2 {
                0%   { transform: translateX(${containerWidth2 + 100}px); }
                100% { transform: translateX(-${textWidth2 + 250}px); }
            }`;
                    document.head.appendChild(styleEl2);

                    el2.style.animationName = 'marqueeDynamic2';
                    el2.style.animationDuration = durationSec2 + 's';
                    el2.style.animationTimingFunction = 'linear';
                    el2.style.animationIterationCount = 'infinite';
                });
            }
        });
    </script>
    @if ($pending)
        <script>
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const tag = e.target.tagName.toLowerCase();
                    if (['input', 'textarea', 'select'].includes(tag)) {
                        const form = document.querySelector('.needs-validation');
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            const firstInvalid = form.querySelector(':invalid');
                            if (firstInvalid) {
                                firstInvalid.focus();
                                firstInvalid.reportValidity();
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endsection
