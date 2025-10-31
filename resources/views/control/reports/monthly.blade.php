@extends('layouts.app')

@push('subtitle')
<p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">Monthly Score Control Member Report</p>
@endpush

@section('content')
<div class="card p-3 shadow-sm">
  <div class="d-flex justify-content-center">
    <div class="col-md-8 border border-1 border-black">
      <canvas id="chart"></canvas>
    </div>
    <div class="col-md-4 border border-1 border-black">
      <table>
        <tr>
          <th>Periode</th>
          <th>:</th>
          <th>[Data]</th>
        </tr>
        <tr>
          <th>Member Name</th>
          <th>:</th>
          <th>[Data]</th>
        </tr>
        <tr>
          <th>ID Member</th>
          <th>:</th>
          <th>[Data]</th>
        </tr>
        <tr>
          <th>Dept</th>
          <th>:</th>
          <th>[Data]</th>
        </tr>
        <tr>
          <th>Bagian</th>
          <th>:</th>
          <th>[Data]</th>
        </tr>
      </table>
      <div class="d-flex p-1 mt-5 gap-1">
        <table class="table table-bordered border-black">
          <tr>
            <td>Disetujui</td>
          </tr>
          <tr>
            <td></td>
          </tr>
          <tr>
            <td>Daniel T</td>
          </tr>
        </table>
        <table class="table table-bordered border-black">
          <tr>
            <td>Diperiksa</td>
          </tr>
          <tr>
            <td></td>
          </tr>
          <tr>
            <td>Febby</td>
          </tr>
        </table>
        <table class="table table-bordered border-black">
          <tr>
            <td rowspan="2">Dibuat</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <div class="row my-3">
    <table class="table table-bordered border-black">
      <thead>
        <tr>
          <th class="text-center">No.</th>
          <th class="text-center">Date</th>
          <th class="text-center">Problem</th>
          <th class="text-center">Countermeasure</th>
          <th class="text-center">Verification Date</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center">1</td>
          <td class="text-center">25 Juli 2025</td>
          <td class="text-center">Operator tidak mengikuti 5 Minutes</td>
          <td class="text-center">Operator diberi sanksi SP 1</td>
          <td class="text-center">29 Juli 2025</td>
          <td class="text-center">Close</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="pt-2 pb-5 d-flex gap-3 justify-content-end align-items-center">
    <a href="#" class="btn btn-primary text-white py-2 px-4">Back</a>
    <button onclick="window.print()" class="btn btn-primary py-2 px-4">Print</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('chart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Awal Shift', 'Saat Kerja', 'Setelah Istirahat', 'Akhir Shift'],
      datasets: [{
        label: 'T.Score',
        backgroundColor: '#f77f00'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          max: 100
        }
      }
    }
  });
</script>
@endsection