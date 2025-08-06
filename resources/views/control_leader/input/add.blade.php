@extends('layouts.control-leader')

@section('title-header')
<p class="fs-2 w-100 border border-1 border-white rounded-2 text-uppercase">
  Add Checksheet
</p>
@endsection

@section('content')
<div class="d-flex w-100 justify-content-between align-items-center">
  <div class="d-flex gap-3">
    <label for="">Nama Checksheet</label>
    <input type="text" id="" name="" placeholder="Nama" />
  </div>
  <div class="d-flex gap-3">
    <label for="">Category</label>
    <input type="text" id="" name="" placeholder="Production/Finishing" />
  </div>
</div>
<div class="d-flex w-100 gap-3">
  <div class="p-2 w-100 border border-2 border-blue rounded-2">
    <div class="px-4 py-2 border border-2 border-blue rounded-2">
      <p>1. Shift</p>
      <ul>
        <li>1</li>
        <li>2</li>
        <li>3</li>
      </ul>
    </div>
    <div class="my-2 px-4 py-2 border border-2 border-blue rounded-2">
      <label for="" class="w-100">2. Nama Operator</label>
      <input type="text" class="" placeholder="Nama Lengkap">
    </div>
  </div>
  <div class="p-2 w-50 border border-2 border-blue rounded-2">
    <div class="p-2 my-2 border border-2 border-blue rounded-2">
      <p>Text Area</p>
    </div>
    <div class="p-2 my-2 border border-2 border-blue rounded-2">
      <p>Radio Group</p>
    </div>
    <div class="p-2 my-2 border border-2 border-blue rounded-2">
      <p>Text Field</p>
    </div>
  </div>
</div>
@endsection

@section('footer-action')
<div class="w-100 d-flex justify-content-between align-items-center mb-5">
  <button class="btn btn-danger rounded-2 px-2 shadow">Clear</button>
  <div class="d-flex gap-2">
    <a href="#" class="btn btn-primary rounded-2 px-2 shadow">Back</a>
    <a href="#" class="btn btn-primary rounded-2 px-2 shadow">Save</a>
  </div>
</div>
@endsection