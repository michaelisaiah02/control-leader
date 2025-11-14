@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
        List Leader Performance Problem <!--  Text Dinamis lewat URL, untuk sementara Statis dulu -->
    </p>
@endpush

@section('content')
<div class="px-5">
    <form id="filterForm" class="w-100 d-flex justify-content-end gap-3 align-items-center mt-1">
        <label for="filterPackage">Filter</label>
        <select id="filterPackage" class="form-select w-auto">
            <option value="">-- Semua</option>
            <option value="open">Open</option>
            <option value="close">Close</option>
            <option value="follow_up">Follow Up +1</option>
            <option value="follow_up_on_delay">Follow Up +1 Delay</option>
        </select>
    </form>

    <div class="table-responsive">
        <table class="mt-1 mb-0 table table-sm table-striped table-bordered" id="sortableTable">
            <thead class="table-primary text-center">
                <tr>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Leader</th>
                    <th scope="col">Operator</th>
                    <th scope="col">Problem</th>
                    <th scope="col">Countermeasure</th>
                    <th scope="col">Due Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8">Tidak ada data.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="py-1 d-flex justify-content-between">
        <div>
            <a href="#" class="btn btn-primary text-white py-2 px-4">Back</a>
        </div>
    </div>
</div>
@endsection