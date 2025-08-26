@extends('layouts.app')

@push('subtitle')
    <p id="title" class="fs-2 w-75 p-0 my-auto sub-judul border-1 border-white rounded-2 text-uppercase">
        @switch($type)
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
        @endswitch
    </p>
@endpush

@section('content')
    <div class="px-5">
        <div class="d-flex w-100 mt-2 justify-content-between align-items-center">
            <p class="border-2 border-white bg-primary rounded-2 text-white py-2 px-4 shadow">Bagian A</p>
            <p class="border-2 border-primary rounded-2 px-2 py-2 shadow">Stopwatch: <span id="stopwatch"
                    class="py-1 px-2 text-danger bg-danger-subtle">00:00</span></p>
        </div>
        <div class="d-flex flex-column w-100">
            <form id="checksheetForm" method="POST" action="{{ route('checksheet.store') }}">
                @csrf

                <div id="form-steps"></div>

                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button type="button" class="btn btn-primary" id="prevBtn">Prev</button>
                    <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                    <button type="submit" class="btn btn-primary d-none" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script type="module">
        let currentStep = 0;
        let formSteps = [];
        let stopwatchInterval;
        let totalSeconds = 0;

        function startStopwatch() {
            stopwatchInterval = setInterval(() => {
                totalSeconds++;
                const m = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
                const s = String(totalSeconds % 60).padStart(2, '0');
                $("#stopwatch").text(`${m}:${s}`);
            }, 1000);
        }

        // ====== jQuery builders ======
        function renderText(field) {
            return $("<div>", {
                    class: "mb-2 p-1 border border-2 border-primary rounded-2"
                })
                .append(
                    $("<label>", {
                        class: "form-label",
                        text: field.label
                    }),
                    $("<input>", {
                        type: "text",
                        class: "form-control bg-warning-subtle",
                        name: field.name,
                        placeholder: field.placeholder || ''
                    })
                );
        }

        function renderSelect(field) {
            const $select = $("<select>", {
                class: "form-select bg-warning-subtle",
                name: field.name
            });
            (field.options || []).forEach(opt => $select.append($("<option>", {
                value: opt,
                text: opt
            })));
            return $("<div>", {
                    class: "mb-3 p-2 border border-2 border-primary rounded-3"
                })
                .append(
                    $("<label>", {
                        class: "form-label",
                        text: field.label
                    }),
                    $select
                );
        }

        function renderRadio(field) {
            const $wrap = $("<div>", {
                    class: "mb-3 p-2 border border-2 border-primary rounded-3"
                })
                .append($("<label>", {
                    class: "form-label",
                    text: field.label
                }));

            (field.options || []).forEach(opt => {
                const $opt = $("<div>", {
                        class: "form-check"
                    })
                    .append(
                        $("<input>", {
                            class: "form-check-input",
                            type: "radio",
                            name: field.name,
                            value: opt,
                            "data-conditional": JSON.stringify(field.conditional || {}),
                        }).on("change", function() {
                            handleConditional(this);
                        }),
                        $("<label>", {
                            class: "form-check-label",
                            text: opt
                        })
                    );
                $wrap.append($opt);
            });

            $wrap.append($("<div>", {
                id: "conditional-" + field.name
            }));
            return $wrap;
        }

        function renderField(field) {
            if (field.type === "text") return renderText(field);
            if (field.type === "select") return renderSelect(field);
            if (field.type === "radio") return renderRadio(field);
            return $("<div>", {
                text: `Unknown field type: ${field.type}`
            });
        }

        function safeStepData() {
            if (!Array.isArray(formSteps) || formSteps.length === 0) return [];
            if (currentStep < 0) currentStep = 0;
            if (currentStep > formSteps.length - 1) currentStep = formSteps.length - 1;
            return formSteps[currentStep] || [];
        }

        function renderStep() {
            const $container = $("#form-steps").empty();

            const stepData = safeStepData();
            if (stepData.length === 0) {
                $container.append(
                    $("<div>", {
                        class: "alert alert-warning",
                        text: "Form belum tersedia / kosong."
                    })
                );
            } else {
                stepData.forEach(f => $container.append(renderField(f)));
            }

            $("#prevBtn").toggle(currentStep > 0);
            $("#nextBtn").toggle(currentStep < formSteps.length - 1);
            $("#saveBtn").toggleClass("d-none", currentStep !== formSteps.length - 1);
        }

        function handleConditional(el) {
            const $el = $(el);
            let conditional = {};
            try {
                conditional = JSON.parse($el.attr("data-conditional") || "{}");
            } catch (e) {}

            const target = $("#conditional-" + el.name).empty();
            const list = conditional[el.value] || [];
            list.forEach(field => {
                if (field.type === "text") {
                    target.append(
                        $("<div>", {
                            class: "mb-3 p-2 border border-2 border-primary rounded-2"
                        })
                        .append(
                            $("<label>", {
                                class: "form-label",
                                text: field.label
                            }),
                            $("<input>", {
                                type: "text",
                                class: "form-control bg-warning-subtle",
                                name: field.name,
                                placeholder: field.placeholder || ''
                            })
                        )
                    );
                }
                if (field.type === "radio") {
                    const $wrap = $("<div>", {
                            class: "mb-3 p-2 border border-2 border-primary rounded-2"
                        })
                        .append($("<label>", {
                            class: "form-label",
                            text: field.label
                        }));
                    (field.options || []).forEach(opt => {
                        $wrap.append(
                            $("<div>", {
                                class: "form-check"
                            })
                            .append(
                                $("<input>", {
                                    class: "form-check-input",
                                    type: "radio",
                                    name: field.name,
                                    value: opt
                                }),
                                $("<label>", {
                                    class: "form-check-label",
                                    text: opt
                                })
                            )
                        );
                    });
                    target.append($wrap);
                }
            });
        }

        $(function() {
            startStopwatch();

            // loading state
            $("#form-steps").html('<div class="text-muted">Loading form…</div>');

            fetch("{{ asset('js/form.json') }}", {
                    cache: "no-store"
                })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    // pastikan key benar & bertipe array
                    formSteps = [data.bagianA_1, data.bagianA_2, data.bagianB_1, data.bagianB_2]
                        .filter(Array.isArray);

                    if (formSteps.length === 0) {
                        console.warn("form.json kosong / key tidak ditemukan:", Object.keys(data));
                    }
                    currentStep = 0;
                    renderStep();
                })
                .catch(err => {
                    console.error("Gagal load form.json:", err);
                    $("#form-steps").html('<div class="alert alert-danger">Gagal memuat form.json</div>');
                });

            $("#nextBtn").on("click", function() {
                if (currentStep < formSteps.length - 1) {
                    currentStep++;
                    renderStep();
                }
            });
            $("#prevBtn").on("click", function() {
                if (currentStep > 0) {
                    currentStep--;
                    renderStep();
                }
            });
        });
    </script>
@endsection
