<!-- Static: Hanya digunakan slide 6 di PPT -->

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
      <p>1. Shift</p>
      <ul>
        <li>1</li>
        <li>2</li>
        <li>3</li>
      </ul>
    </div>
    <div class="p-1 w-100 border border-2 border-primary rounded-2">
      <label for="" class="form-label">2. ID & Nama Operator</label>
      <select name="" id="" class="form-select bg-warning-subtle" aria-label="Default select example">
        <option value="" disabled selected>Nama Lengkap</option>
        <option value="">A</option>
      </select>
    </div>
    <div class="p-1 w-100 border border-2 border-primary rounded-2">
      <label for="" class="form-label">3. Bagian</label>
      <input type="text" class="form-control bg-warning-subtle" placeholder="Contoh: Cutting">
    </div>
  </div>

  <div class="w-100 d-flex justify-content-end mt-3">
    <div class="d-flex gap-2">
      <a href="#" class="btn btn-primary rounded-2 px-5 border border-2 border-white shadow">Back</a>
      <a href="#" class="btn btn-primary rounded-2 px-5 border border-2 border-white shadow">Next</a>
    </div>
  </div>
</div>
@endsection