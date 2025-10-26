@extends('layouts.app')

@push('subtitle')
<p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
  add question
</p>
@endpush

@section('styles')
<style>
  .cursor-grab {
    cursor: grab;
  }

  .sortable-ghost {
    opacity: 0.4;
    background: #f0f0f0;
  }
</style>
@endsection

@section('content')
<form method="POST" action="{{ route('question.store') }}" class="px-5">
  @csrf
  <!-- Header -->
  <div class="d-flex gap-5 w-100 mt-2 justify-content-between align-items-center my-2">
    <div class="d-flex align-items-center gap-2 w-100">
      <label for="question_text" class="form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Pertanyaan</label>
      <input id="question_text" type="text" name="question_text" class="form-control bg-warning-subtle" required>
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
      <label for="package" class="form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Type</label>
      <select name="package" id="package" class="form-select bg-warning-subtle" required>
        <option value="" selected disabled>-- Pilih</option>
        <option value="awal_shift">Awal Shift</option>
        <option value="saat_bekerja">Saat Bekerja</option>
        <option value="setelah_istirahat">Setelah Istirahat</option>
        <option value="akhir_shift">Akhir Shift</option>
        <option value="leader">Leader</option>
      </select>
    </div>
  </div>

  <div class="row my-2">
    <!-- Kolom Kiri -->
    <div class="col-md-8">
      <div id="builder" class="border border-primary p-3 rounded overflow-y-scroll"
        style="height: 300px; max-height:300px;">
        <!-- Opsi Pilihan -->
        <div class="mb-3 border border-1 border-primary p-3 rounded field-block">
          <label class="form-label my-2">Pilihan:</label>
          <div class="radio-options sortable-radio">
            <div class="input-group mb-1">
              <span class="input-group-text cursor-grab">☰</span>
              <div class="input-group-text">
                <input type="radio" disabled>
              </div>
              <input type="text"
                class="form-control border border-1 border-primary bg-warning-subtle radio-input"
                name="choices[]" value="Option 1" required>
              <button type="button" class="btn btn-outline-danger btn-sm remove-radio"
                onclick="removeRadio(this)">✕</button>
            </div>
            <div class="input-group mb-1">
              <span class="input-group-text cursor-grab">☰</span>
              <div class="input-group-text">
                <input type="radio" disabled>
              </div>
              <input type="text"
                class="form-control border border-1 border-primary bg-warning-subtle radio-input"
                name="choices[]" value="Option 2" required>
              <button type="button" class="btn btn-outline-danger btn-sm remove-radio"
                onclick="removeRadio(this)">✕</button>
            </div>
          </div>
          <p class="text-warning-subtle">*Urutkan yang terbaik ke terburuk</p>
        </div>

        <!-- Problem dan Countermeasure -->
        <div class="mb-3 border border-1 border-primary p-3 rounded field-block">
          <label for="problem_label" class="form-label my-2">Label Problem</label>
          <input type="text" id="problem_label" name="problem_label" class="form-control border border-1 border-primary bg-warning-subtle" required>
        </div>
        <div class="mb-3 border border-1 border-primary p-3 rounded field-block">
          <label for="countermeasure_label" class="form-label my-2">Label Countermeasure</label>
          <input type="text" id="countermeasure_label" name="countermeasure_label" class="form-control border border-1 border-primary bg-warning-subtle" required>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-md-4">
      <div class="d-grid gap-2 border border-primary p-3 rounded h-100">
        <button type="button" class="btn btn-outline-primary add-field" data-type="radio">Tambah Pilihan</button>
        <button type="button" class="btn btn-outline-primary active add-field" data-type="toggle">Problem dan Countermeasure</button>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="py-1 d-flex justify-content-between">
    <div>
      <button id="clear-button" type="button" class="btn btn-danger text-white py-2 px-4">Clear</button>
    </div>
    <div>
      <a href="{{ route('question.index') }}" class="btn btn-primary text-white py-2 px-4">Back</a>
      <button type="submit" class="btn btn-primary text-white py-2 px-4">Save</button>
    </div>
  </div>
</form>
<x-toast />
@endsection

@section('scripts')
<script src="{{ asset('js/Sortable.min.js') }}"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const builder = document.getElementById("builder");
    const radioContainer = builder.querySelector(".radio-options");
    const toggleButton = document.querySelector('[data-type="toggle"]');
    const addRadioButton = document.querySelector('[data-type="radio"]');
    const resetButton = document.querySelector('#clear-button');

    // Drag Radio
    Sortable.create(radioContainer, {
      animation: 150,
      handle: ".cursor-grab",
      ghostClass: "sortable-ghost"
    });

    // Clear Button
    resetButton.addEventListener("click", () => {
      if (!confirm("Apakah Anda yakin? Semua field akan dihapus.")) return;

      builder.innerHTML = `
        <div class="mb-3 border border-1 border-primary p-3 rounded field-block">
          <label>Pilihan:</label>
          <div class="radio-options sortable-radio">
            <div class="input-group mb-1">
              <span class="input-group-text cursor-grab">☰</span>
              <div class="input-group-text">
                <input type="radio" disabled>
              </div>
              <input
                type="text"
                class="form-control border border-1 border-primary bg-warning-subtle radio-input"
                name="choices[]"
                value="Option 1" required>
              <button type="button" class="btn btn-outline-danger btn-sm remove-radio" onclick="removeRadio(this)">✕</button>
            </div>
            <div class="input-group mb-1">
              <span class="input-group-text cursor-grab">☰</span>
              <div class="input-group-text">
                <input type="radio" disabled>
              </div>
              <input
                type="text"
                class="form-control border border-1 border-primary bg-warning-subtle radio-input"
                name="choices[]"
                value="Option 2"
                required>
              <button type="button" class="btn btn-outline-danger btn-sm remove-radio" onclick="removeRadio(this)">✕</button>
            </div>
          </div>
          <p class="text-warning-subtle">*Urutkan yang terbaik ke terburuk</p>
        </div>
      `;
    });

    // Tambah Radio
    addRadioButton.addEventListener("click", () => {
      const index = radioContainer.querySelectorAll(".radio-input").length + 1;

      const newOption = `
      <div class="input-group mb-1">
        <span class="input-group-text cursor-grab">☰</span>
        <div class="input-group-text"><input type="radio" disabled></div>
        <input
          type="text"
          class="form-control border border-1 border-primary bg-warning-subtle radio-input"
          name="choices[]"
          value="Option ${index}"
          required
        >
        <button type="button" class="btn btn-outline-danger btn-sm remove-radio">✕</button>
      </div>
    `;

      radioContainer.insertAdjacentHTML("beforeend", newOption);
    });

    // Delete Radio
    builder.addEventListener("click", (e) => {
      if (e.target.classList.contains("remove-radio")) {
        const allRadios = builder.querySelectorAll(".radio-options .input-group");

        if (allRadios.length <= 2) {
          alert("Minimal harus ada 2 opsi radio!");
          return;
        }

        e.target.closest(".input-group").remove();
      }
    });

    // Toggle Problem and Countermeasure
    toggleButton.addEventListener("click", () => {
      const problemField = builder.querySelector('input[name="problem_label"]');
      const counterField = builder.querySelector('input[name="countermeasure_label"]');

      // Show: Problem and Countermeasure
      if (!problemField && !counterField) {
        builder.insertAdjacentHTML("beforeend", `
        <div class="mb-3 border border-1 border-primary p-3 rounded field-block">
          <label for="problem_label" class="form-label my-2">Label Problem</label>
          <input type="text" id="problem_label" name="problem_label" class="form-control border border-1 border-primary bg-warning-subtle" required>
        </div>
        <div class="mb-3 border border-1 border-primary p-3 rounded field-block">
          <label for="countermeasure_label" class="form-label my-2">Label Countermeasure</label>
          <input type="text" id="countermeasure_label" name="countermeasure_label" class="form-control border border-1 border-primary bg-warning-subtle" required>
        </div>
      `);
        toggleButton.classList.add('active');
        return;
      }

      // Toggle Hide/Show
      const fields = builder.querySelectorAll(
        'input[name="problem_label"], input[name="countermeasure_label"]');
      fields.forEach(input => input.closest(".field-block").remove());
      toggleButton.classList.remove('active');
    });
  });
</script>
@endsection