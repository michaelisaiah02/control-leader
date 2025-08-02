@extends('layouts.control-leader')

@section('title-header')
<p class="fs-2 w-100 border border-1 border-white rounded-2 text-uppercase">
  List
</p>
@endsection

@section('content')
<table class="table table-bordered border-dark">
  <thead class="table-primary">
    <tr>
      <th>No</th>
      <th>Nama Checksheet</th>
      <th>Category</th>
      <th class="text-center">Date Created</th>
      <th colspan="3" class="text-center">Action</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>Control Leader Awal Shift</td>
      <td>Production</td>
      <td class="text-center">22/01/2025</td>
      <td class="text-center">Edit</td>
      <td class="text-center">Duplicate</td>
      <td class="text-center">Delete</td>
    </tr>
  </tbody>
</table>
@endsection

@section('footer-action')
<div class="w-100 d-flex justify-content-between align-items-center mb-5">
  <a
    href="./list/index.html"
    class="btn btn-primary rounded-circle fs-1 p-4">
    &plus;
  </a>
  <a href="../index.html" class="btn btn-primary" style="width: 124px">
    Back
  </a>
</div>
@endsection