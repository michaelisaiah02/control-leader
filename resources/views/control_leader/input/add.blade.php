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