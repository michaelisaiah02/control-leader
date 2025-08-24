@extends('layouts.app')

@push('subtitle')
<p id="title" class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
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
  <div class="d-flex w-100 my-2 justify-content-between align-items-center">
    <p class="border border-2 border-white bg-primary rounded-2 text-white py-1 px-4 shadow">Bagian A</p>
    <p class="border border-2 border-primary rounded-2 px-2 py-1 shadow">Stopwatch: <span id="stopwatch" class="py-1 px-2 text-danger bg-danger-subtle">00:00</span></p>
  </div>
  <div class="d-flex flex-column w-100 gap-3">
    <form id="checksheetForm" method="POST" action="{{ route('checksheet.store') }}">
      @csrf

      <div id="form-steps"></div>

      <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-primary" id="prevBtn">Prev</button>
        <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
        <button type="submit" class="btn btn-primary d-none" id="saveBtn">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  let currentStep = 0;
  let formSteps = [];
  let stopwatchInterval;
  let totalSeconds = 0;

  function startStopwatch() {
    stopwatchInterval = setInterval(() => {
      totalSeconds++;
      let minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
      let seconds = String(totalSeconds % 60).padStart(2, '0');
      document.getElementById("stopwatch").textContent = `${minutes}:${seconds}`;
    }, 1000);
  }
  startStopwatch();

  fetch("{{ asset('js/form.json') }}")
    .then(response => response.json())
    .then(data => {
      formSteps = [data.bagianA_1, data.bagianA_2, data.bagianB_1, data.bagianB_2];
      renderStep();
    });

  function renderStep() {
    let container = document.getElementById("form-steps");
    container.innerHTML = "";
    let stepData = formSteps[currentStep];

    stepData.forEach(field => {
      let fieldHtml = "";

      if (field.type === "text") {
        fieldHtml = `
                    <div class="mb-3 p-2 border border-2 border-primary rounded-2">
                        <label class="form-label">${field.label}</label>
                        <input type="text" class="form-control bg-warning-subtle" name="${field.name}" placeholder="${field.placeholder || ''}">
                    </div>`;
      }

      if (field.type === "select") {
        fieldHtml = `
                    <div class="mb-3 p-2 border border-2 border-primary rounded-2">
                        <label class="form-label">${field.label}</label>
                        <select class="form-select bg-warning-subtle" name="${field.name}">
                            ${field.options.map(opt => `<option value="${opt}">${opt}</option>`).join("")}
                        </select>
                    </div>`;
      }

      if (field.type === "radio") {
        fieldHtml = `
                    <div class="mb-3 p-2 border border-2 border-primary rounded-2">
                        <label class="form-label">${field.label}</label>
                        ${field.options.map(opt => `
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="${field.name}" value="${opt}" data-conditional='${JSON.stringify(field.conditional || {})}' onchange="handleConditional(this)">
                                <label class="form-check-label">${opt}</label>
                            </div>`).join("")}
                    </div>
                    <div id="conditional-${field.name}"></div>`;
      }

      container.innerHTML += fieldHtml;
    });

    document.getElementById("prevBtn").style.display = currentStep > 0 ? "inline-block" : "none";
    document.getElementById("nextBtn").style.display = currentStep < formSteps.length - 1 ? "inline-block" : "none";
    document.getElementById("saveBtn").classList.toggle("d-none", currentStep !== formSteps.length - 1);
  }

  function handleConditional(el) {
    let name = el.name;
    let value = el.value;
    let conditional = {};

    try {
      conditional = JSON.parse(el.getAttribute("data-conditional"));
    } catch (err) {
      console.error(err);
    }

    let container = document.getElementById(`conditional-${name}`);
    container.innerHTML = "";

    if (conditional[value]) {
      conditional[value].forEach(field => {
        if (field.type === "text") {
          container.innerHTML += `
                        <div class="mb-3 p-2 border border-2 border-primary rounded-2">
                            <label class="form-label">${field.label}</label>
                            <input type="text" class="form-control bg-warning-subtle" name="${field.name}" placeholder="${field.placeholder || ''}">
                        </div>`;
        }

        if (field.type === "radio") {
          container.innerHTML += `
                        <div class="mb-3 p-2 border border-2 border-primary rounded-2">
                            <label class="form-label">${field.label}</label>
                            ${field.options.map(opt => `
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="${field.name}" value="${opt}">
                                    <label class="form-check-label">${opt}</label>
                                </div>`).join("")}
                        </div>`;
        }
      });
    }
  }

  document.getElementById("nextBtn").addEventListener("click", () => {
    currentStep++;
    renderStep();
  });

  document.getElementById("prevBtn").addEventListener("click", () => {
    currentStep--;
    renderStep();
  });
</script>
@endsection