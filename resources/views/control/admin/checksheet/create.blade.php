@extends('layouts.app')

@push('subtitle')
<p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
  {{ isset($question) ? 'edit question' : 'add question' }}
</p>
@endpush

@section('content')
<div class="px-5">
  <div class="d-flex gap-5 w-100 mt-2 justify-content-between align-items-center my-2">
    <div class="d-flex align-items-center gap-2 w-100">
      <label for="name" class="form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Pertanyaan</label>
      <input id="name" type="text" name="" class="form-control bg-warning-subtle">
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
      <label for="category" class="form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Type</label>
      <select name="" id="category" class="form-control bg-warning-subtle">
        <option value="" selected disabled>-- Pilih</option>
        <option value="op_awal">Awal Shift</option>
        <option value="op_kerja">Saat Bekerja</option>
        <option value="op_istirahat">Setelah Istirahat</option>
        <option value="op_akhir">Akhir Shift</option>
      </select>
    </div>
  </div>
  <div class="row my-4">
    <!-- Kolom Kiri -->
    <div class="col-md-8">
      <div id="builder" class="border border-primary p-3 rounded" style="min-height:300px;">
      </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-md-4">
      <div class="d-grid gap-2 border border-primary p-3 rounded h-100">
        <button class="btn btn-outline-primary add-field" data-type="toggle">Toggle (Boolean)</button>
        <button class="btn btn-outline-primary add-field" data-type="radio">Tambah Pilihan</button>
      </div>
    </div>
  </div>

  <div class="py-1 d-flex justify-content-between">
    <div>
      <a href="" class="btn btn-danger text-white py-2 px-4">Clear</a>
    </div>
    <div>
      <a href="" class="btn btn-primary text-white py-2 px-4">Back</a>
      <button id="updateBtn" class="btn btn-primary text-white py-2 px-4">Update</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    let count = 0;
    let options = [];
    const builder = document.getElementById("builder");
    const saveBtn = document.getElementById("saveBtn");

    // Tambah Field
    document.querySelectorAll(".add-field").forEach(btn => {
      btn.addEventListener("click", () => addField(btn.dataset.type));
    });

    // Tooltips
    function initTooltips() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.map(el => new bootstrap.Tooltip(el))
    }

    document.addEventListener("DOMContentLoaded", initTooltips);

    function addRadio(btn) {
      const container = btn.previousElementSibling;
      const index = container.querySelectorAll(".radio-input").length + 1;
      container.insertAdjacentHTML("beforeend", `
        <div class="input-group mb-1">
            <span class="input-group-text cursor-grab">☰</span>
            <div class="input-group-text">
                <input type="radio" disabled 
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       title="Klik untuk memilih opsi ini">
            </div>
            <input type="text" class="form-control radio-input" value="Option ${index}">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRadio(this)">✕</button>
        </div>
    `);
      enableSortable();
      initTooltips(); // refresh tooltip
    }

    function addField(type) {
      let fieldHtml = "";
      const label = `Field`;

      switch (type) {
        case "toggle":
          fieldHtml = `
                    <div class="mb-3 field-block">
                        <label contenteditable="true">Problem</label>
                        <input type="text" class="form-control">
                    </div>
                    <div class="mb-3 field-block">
                        <label contenteditable="true">Countermeasture</label>
                        <input type="text" class="form-control">
                    </div>`;
          break;
        case "radio":
          fieldHtml = `
                    <div class="mb-3 field-block" data-type="radio">
                        <label contenteditable="true">${label}</label>
                        <div class="radio-opjtions">
                          ${(options.length ? options : ["Option 1", "Option 2"]).map(opt => `
                              <div class="input-group mb-1">
                                  <div class="input-group-text">
                                      <input type="radio" disabled
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top"
                                        title="Klik untuk memilih opsi ini">
                                  </div>
                                  <input type="text" class="form-control radio-input" value="${opt}">
                                  <button type="button" class="btn btn-outline-danger btn-sm remove-radio">✕</button>
                              </div>
                          `).join("")}
                      </div>
                      <button type="button" class="btn btn-outline-primary btn-sm add-radio mt-1">+ Add Radio</button>
                    </div>`;
          break;
      }

      builder.insertAdjacentHTML("beforeend", fieldHtml);
      count++;
    }

    // Add Radio
    builder.addEventListener("click", e => {
      if (e.target.classList.contains("add-radio")) {
        const container = e.target.closest(".field-block").querySelector(".radio-options");
        const index = container.querySelectorAll(".radio-input").length + 1;

        container.insertAdjacentHTML("beforeend", `
            <div class="input-group mb-1">
                <div class="input-group-text">
                    <input type="radio" disabled>
                </div>
                <input type="text" class="form-control radio-input" value="Option ${index}">
                <button type="button" class="btn btn-outline-danger btn-sm remove-radio">✕</button>
            </div>
        `);
      }

      // Remove Radio
      if (e.target.classList.contains("remove-radio")) {
        e.target.closest(".input-group").remove();
      }
    });

    function collectFields() {
      let fields = [];
      builder.querySelectorAll(".field-block").forEach(div => {
        const label = div.querySelector("label").innerText;
        const type = div.dataset.type || "text";

        if (type === "radio") {
          let options = [];
          div.querySelectorAll(".radio-input").forEach(input => options.push(input.value));
          fields.push({
            label,
            type,
            options
          });
        } else {
          fields.push({
            label,
            type,
            options: null
          });
        }
      });
      return fields;
    }

    // Save Fields
    saveBtn.addEventListener("click", () => {
      let fields = [];
      builder.querySelectorAll(".field-block").forEach(div => {
        const label = div.querySelector("label").innerText;
        const input = div.querySelector("input,textarea");
        let type = input ? input.tagName.toLowerCase() : "radio";

        fields.push({
          label: label,
          type: type === "input" ? "text" : type,
          options: type === "radio" ? ["Option 1", "Option 2"] : null
        });
      });

      fetch("/checksheet", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            fields
          })
        })
        .then(res => res.json())
        .then(data => alert("Checksheet saved!"))
        .catch(err => console.error(err));
    });
  });
</script>
@endsection