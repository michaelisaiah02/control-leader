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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Chart</title>
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
          CHART
        </p>
      </div>
      <a class="navbar-brand mx-0 mx-md-4" href="/">
        <img src="{{ asset('image/logo-rice.png') }}" alt="Logo" class="mt-0 logo">
      </a>
    </div>
  </nav>
  <div class="px-5 d-flex gap-5 align-items-center">
    <div>
      <h2 class="border border-2 border-white bg-primary rounded-2 text-white py-1 px-4 shadow">Daily</h2>
      <!-- Daily Chart -->
      <div class="card p-3 shadow">
        <h5 class="text-center">John Doe</h5>
        <canvas id="dailyChart"></canvas>
      </div>
    </div>
    <div class="row">
      <!-- Monthly Chart -->
      <div class="w-100 mb-4">
        <div class="card p-3 shadow">
          <h5 class="text-center">Monthly Report John Doe</h5>
          <canvas id="monthlyChart"></canvas>
        </div>
      </div>
      <div class="card shadow">
        <div class="card-body p-0">
          <table class="table table-bordered mb-0">
            <thead>
              <tr>
                <th>No</th>
                <th>Date</th>
                <th>Problem</th>
                <th>Countermeasure</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>2 Juni 2025</td>
                <td>Mesin macet</td>
                <td>Reset mesin</td>
                <td>Open</td>
              </tr>
              <tr>
                <td>2</td>
                <td>2 Juni 2025</td>
                <td>Bahan kurang</td>
                <td>Tambah stok</td>
                <td>Close</td>
              </tr>
              <tr>
                <td>3</td>
                <td>2 Juni 2025</td>
                <td>-</td>
                <td>-</td>
                <td>Open</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script>
    // Daily chart
    // new Chart(document.getElementById('dailyChart'), {
    //   type: 'bar',
    //   data: {
    //     labels: dailyData.map(d => d.shift),
    //     datasets: [{
    //         label: 'Score',
    //         data: dailyData.map(d => d.score),
    //         backgroundColor: 'rgba(54, 162, 235, 0.7)'
    //       },
    //       {
    //         label: 'Target',
    //         data: dailyData.map(d => d.target),
    //         type: 'line',
    //         borderColor: 'rgba(255, 99, 132, 1)',
    //         borderWidth: 2,
    //         fill: false
    //       }
    //     ]
    //   },
    //   options: {
    //     responsive: true,
    //     scales: {
    //       y: {
    //         beginAtZero: true,
    //         max: 100
    //       }
    //     }
    //   }
    // });

    // // Monthly chart
    // new Chart(document.getElementById('monthlyChart'), {
    //   type: 'bar',
    //   data: {
    //     labels: monthlyData.map(d => d.day),
    //     datasets: [{
    //         label: 'Score',
    //         data: monthlyData.map(d => d.score),
    //         backgroundColor: 'rgba(75, 192, 192, 0.7)'
    //       },
    //       {
    //         label: 'Target',
    //         data: monthlyData.map(d => d.target),
    //         type: 'line',
    //         borderColor: 'rgba(255, 159, 64, 1)',
    //         borderWidth: 2,
    //         fill: false
    //       }
    //     ]
    //   },
    //   options: {
    //     responsive: true,
    //     scales: {
    //       y: {
    //         beginAtZero: true,
    //         max: 100
    //       }
    //     }
    //   }
    // });

    new Chart(document.getElementById('dailyChart'), {
      type: 'bar',
      data: {
        labels: ['Awal Shift', 'Saat Kerja', 'Setelah Istirahat', 'Akhir Shift'],
        datasets: [{
            label: 'Score',
            data: [94, 93, 89, 97],
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
          },
          {
            label: 'Target',
            data: [95, 95, 95, 95],
            type: 'line',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 2,
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: false,
            min: 84,
            max: 100
          }
        }
      }
    });

    // Monthly chart dummy
    new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: Array.from({
          length: 31
        }, (_, i) => i + 1),
        datasets: [{
            label: 'Score',
            data: Array.from({
              length: 31
            }, () => Math.floor(Math.random() * 10) + 90),
            backgroundColor: 'rgba(75, 192, 192, 0.7)'
          },
          {
            label: 'Target',
            data: Array(31).fill(95),
            type: 'line',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 2,
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: false,
            min: 80,
            max: 100
          }
        }
      }
    });
  </script>
</body>

</html>