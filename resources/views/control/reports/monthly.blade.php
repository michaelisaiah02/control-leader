<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
  @if (!request()->is('login'))
  <style>
    #navbar-kalibrasi {
      border-bottom-left-radius: 180px;
      border-bottom-right-radius: 180px;
    }

    #title-section {
      height: 10rem
    }
  </style>
  @endif
  <title>Monthly Score Control Member Report</title>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light text-light @if (request()->is('login')) bg-transparent px-3 @else mx-5 px-5 pb-3 bg-primary @endif"
    id="navbar-kalibrasi">
    <div class="container-fluid justify-content-center">
      <a class="navbar-brand mx-0 mx-md-4" href="/">
        <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" class="mt-0 logo">
      </a>
      <div class="row text-center justify-content-center" id="title-section">
        <p id="main-title" class="align-self-center main-title p-0 m-0 text-uppercase">CONTROL LEADER</p>
        <p class="align-self-center company-name p-0 m-0">PT. CATURINDO AGUNGJAYA RUBBER</p>
        <p id="title" class="fs-2 w-75 p-0 my-auto sub-judul border border-1 border-white rounded-2 text-uppercase">
          Monthly Score Control Member Report
        </p>
      </div>
      <a class="navbar-brand mx-0 mx-md-4" href="/">
        <img src="{{ asset('image/logo-rice.png') }}" alt="Logo" class="mt-0 logo">
      </a>
    </div>
  </nav>
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
</body>

</html>