@extends('layouts.app')

@push('subtitle')
<p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
  edit checksheet
</p>
@endpush

@section('content')
<div class="px-5">
  <div class="d-flex gap-5 w-100 mt-2 justify-content-between align-items-center my-2">
    <div class="d-flex align-items-center gap-2 w-100">
      <label for="name" class="form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Checksheet</label>
      <input id=" name" type="text" placeholder="Nama" class="form-control bg-warning-subtle">
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
      <label for="category" class="form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Nama Checksheet</label>
      <select name="" id="category" class="form-control bg-warning-subtle">
        <option value="" selected>Production/Finishing</option>
        <option value=""></option>
        <option value=""></option>
      </select>
    </div>
  </div>
  <div class="row my-4">
    <!-- Kolom Kiri -->
    <div class="col-md-8">
      <div id="builder" class="border p-3 rounded" style="min-height:300px;">
      </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-md-4">
      <div class="d-grid gap-2">
        <button class="btn btn-outline-primary add-field" data-type="text">Text Field</button>
        <button class="btn btn-outline-primary add-field" data-type="textarea">Text Area</button>
        <button class="btn btn-outline-primary add-field" data-type="radio">Radio Group</button>
      </div>
    </div>
  </div>

  <div class="py-1 d-flex justify-content-between">
    <div>
      <a href="" class="btn btn-danger text-white py-2 px-4">Clear</a>
    </div>
    <div>
      <button id="saveBtn" class="btn btn-primary text-white py-2 px-4">Save</button>
      <a href="" class="btn btn-primary text-white py-2 px-4">Back</a>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  const existingFields = @json($fields);

  document.addEventListener("DOMContentLoaded", () => {
    let count = 1;
    const builder = document.getElementById("builder");
    const saveBtn = document.getElementById("saveBtn");
    const updateBtn = document.getElementById("updateBtn");

    // Tambah Field
    document.querySelectorAll(".add-field").forEach(btn => {
      btn.addEventListener("click", () => addField(btn.dataset.type));
    });

    function addField(type, label = null, options = []) {
      let fieldHtml = "";
      const fieldLabel = label || `Field ${count}`;

      switch (type) {
        case "text":
          fieldHtml = `
                    <div class="mb-3 field-block">
                        <label contenteditable="true">${count}. ${fieldLabel}</label>
                        <input type="text" class="form-control">
                    </div>`;
          break;

        case "textarea":
          fieldHtml = `
                    <div class="mb-3 field-block">
                        <label contenteditable="true">${count}. ${fieldLabel}</label>
                        <textarea class="form-control"></textarea>
                    </div>`;
          break;

        case "radio":
          fieldHtml = `
                    <div class="mb-3 field-block">
                        <label contenteditable="true">${count}. ${fieldLabel}</label>
                        ${(options.length ? options : ["Option 1","Option 2"]).map(opt => `
                            <div><input type="radio" name="opt${count}"> ${opt}</div>
                        `).join("")}
                    </div>`;
          break;
      }

      builder.insertAdjacentHTML("beforeend", fieldHtml);
      count++;
    }

    // Jika edit mode, render existingFields
    if (typeof existingFields !== "undefined") {
      existingFields.forEach(f => addField(f.type, f.label, f.options));
    }

    // Kumpulkan data fields
    function collectFields() {
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
      return fields;
    }

    // Save baru
    if (saveBtn) {
      saveBtn.addEventListener("click", () => {
        fetch("/checksheet", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              fields: collectFields()
            })
          })
          .then(res => res.json())
          .then(() => alert("Checksheet created!"));
      });
    }

    // Update
    if (updateBtn) {
      updateBtn.addEventListener("click", () => {
        const url = window.location.pathname; // /checksheet/{id}/edit
        const id = url.split("/")[2];

        fetch(`/checksheet/${id}`, {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              fields: collectFields()
            })
          })
          .then(res => res.json())
          .then(() => alert("Checksheet updated!"));
      });
    }
  });
</script>
@endsection