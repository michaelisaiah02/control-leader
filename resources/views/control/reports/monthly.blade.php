<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
  <title>Monthly Score Control Member Report</title>
</head>

<body>
  <div class="w-100">
    <div class="d-flex w-100 justify-content-between align-items-stretch">
      <a class="border border-black" href="/">
        <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" class="mt-0 logo">
      </a>
      <div class="border border-black w-100 d-flex justify-content-center align-items-center">
        <p class="text-center" style="text-transform: capitalize; font-size: 2rem">Monthly Consistency {{ $type }} Report</p>
      </div>
      <a class="border border-black" href="/">
        <img src="{{ asset('image/logo-rice.png') }}" alt="Logo" class="mt-0 logo">
      </a>
    </div>
  </div>
  <div class="card p-3 shadow-sm">
    <div class="d-flex justify-content-center">
      <div class="col-md-8 col-sm-8 border border-1 border-black">
        <canvas id="chart"></canvas>
      </div>
      <div class="col-md-4 border border-1 border-black d-flex flex-column justify-content-between">
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
              <td style="height: 80px;"></td>
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
              <td style="height: 80px;"></td>
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
          @forelse ($problems as $problem)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $problem->created_at }}</td>
            <td class="text-center">{{ $problem->problem }}</td>
            <td class="text-center">{{ $problem->countermeasure }}</td>
            <td class="text-center">{{ $problem->due_date }}</td>
            <td class="text-center">{{ $problem->status }}</td>
          </tr>
          @empty
          <tr>
            <td class="text-center">1</td>
            <td class="text-center">25 Juli 2025</td>
            <td class="text-center">Operator tidak mengikuti 5 Minutes</td>
            <td class="text-center">Operator diberi sanksi SP 1</td>
            <td class="text-center">29 Juli 2025</td>
            <td class="text-center">Close</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pt-2 pb-5 d-flex gap-3 justify-content-end align-items-center">
      <a href="{{ route('control.reports.form', ['type' => $type]) }}" class="btn btn-primary text-white py-2 px-4">Back</a>
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