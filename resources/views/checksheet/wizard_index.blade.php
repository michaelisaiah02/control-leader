<!-- Dynamic: slide 7 dan 8 di PPT -->

@extends('layouts.app')

@push('subtitle')
<p id="title" class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
  Judul Checksheet
</p>
@endpush

@section('content')
<div class="px-5">
  <div class="d-flex w-100 my-2 justify-content-between align-items-center">
    <p class="border border-2 border-white bg-primary rounded-2 text-white py-1 px-4 shadow">Bagian A</p>
    <p class="border border-2 border-primary rounded-2 px-2 py-1 shadow">Stopwatch: <span class="py-1 px-2 text-danger bg-danger-subtle">00:00</span></p>
  </div>
  <div class="d-flex flex-column w-100 gap-3">
    <div class="p-2 w-100 border border-2 border-primary rounded-2">
      <p> 1. Check kehadiran operator <br /> Apabila operator tidak masuk: </p>
      <ul>
        <li>Isi perubahan Man Power di Henkaten Board</li>
        <li>Operator pengganti harus sesuai Skill Map</li>
        <li>Konfirmasi hasil awal untuk operator pengganti (Hasil awal kerja OK)</li>
      </ul>
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="status" id="status-absen" value="absen">
          <label class="form-check-label" for="status-absen">0) Absen</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="status" id="status-hadir" value="hadir">
          <label class="form-check-label" for="status-hadir">1) Hadir</label>
        </div>
      </div>

    </div>

    @foreach ($steps as $step)
    <x-forms.wizard :id="$step['id']" :name="$step['name']" :type="$step['type']" :placeholder="$step['placeholder'] ?? ''" :label="$step['label']" :options="$step['options'] ?? []" />
    @endforeach
  </div>

  <div class="w-100 d-flex justify-content-end mt-3">
    <div class="d-flex gap-2">
      <a href="#" class="btn btn-primary rounded-2 px-5 border border-2 border-white shadow">Back</a>
      <a href="#" class="btn btn-primary rounded-2 px-5 border border-2 border-white shadow">Next</a>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const absenRadio = document.getElementById("status-absen");
    const hadirRadio = document.getElementById("status-hadir");

    const steps = @json(array_column($steps, 'id'));

    function hideAll() {
      steps.forEach(id => document.getElementById(id).classList.add("d-none"));
    }

    absenRadio.addEventListener("change", function() {
      if (this.checked) {
        hideAll();
        document.getElementById(steps[0]).classList.remove("d-none");
      }
    });

    hadirRadio.addEventListener("change", function() {
      if (this.checked) hideAll();
    });

    steps.forEach((id, index) => {
      const stepEl = document.getElementById(id);
      const inputEl = stepEl.querySelector("input");

      if (!inputEl) return;

      inputEl.addEventListener("input", function() {
        if (this.value.trim() !== "" || this.checked) {
          if (steps[index + 1]) {
            document.getElementById(steps[index + 1]).classList.remove("d-none");
          }
        } else {
          for (let i = index + 1; i < steps.length; i++) {
            document.getElementById(steps[i]).classList.add("d-none");
          }
        }
      });

      // handle radio khusus (pilihan otomatis membuka next)
      if (inputEl.type === "radio") {
        stepEl.querySelectorAll("input[type=radio]").forEach(radio => {
          radio.addEventListener("change", function() {
            if (steps[index + 1]) {
              document.getElementById(steps[index + 1]).classList.remove("d-none");
            }
          });
        });
      }
    });
  });
</script>
@endsection