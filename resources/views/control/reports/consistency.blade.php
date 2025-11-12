<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
  <style>
    #navbar-kalibrasi {
      border-bottom-left-radius: 180px;
      border-bottom-right-radius: 180px;
    }

    #title-section {
      height: 10rem
    }
  </style>
  <title>Leader Consistency Control Member Report</title>
</head>

<body>
  <div class="row mb-3">
    <div class="col-md-2">
      <select id="month" class="form-select">
        @for ($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" @selected($m==now()->month)>{{ $m }}</option>
          @endfor
      </select>
    </div>
    <div class="col-md-2">
      <select id="year" class="form-select">
        @for ($y = now()->year - 5; $y <= now()->year + 1; $y++)
          <option value="{{ $y }}" @selected($y==now()->year)>{{ $y }}</option>
          @endfor
      </select>
    </div>
  </div>
  <div id="chart"></div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      async function loadConsistency() {
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;

        const res = await fetch(`/api/reports/leader-consistency?month=${month}&year=${year}`);
        const json = await res.json();

        chart.data.labels = json.labels;
        chart.data.datasets[0].data = json.data.awal_shift;
        chart.data.datasets[1].data = json.data.bekerja;
        chart.data.datasets[2].data = json.data.istirahat;
        chart.data.datasets[3].data = json.data.akhir_shift;
        chart.update();
      }

      const chart = new Chart(document.getElementById('chart').getContext('2d'), {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
              label: 'Awal Shift',
              data: [],
              borderWidth: 2,
              borderColor: 'red'
            },
            {
              label: 'Saat Bekerja',
              data: [],
              borderWidth: 2,
              borderColor: 'blue'
            },
            {
              label: 'Setelah Istirahat',
              data: [],
              borderWidth: 2,
              borderColor: 'green'
            },
            {
              label: 'Akhir Shift',
              data: [],
              borderWidth: 2,
              borderColor: 'orange'
            },
          ]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      loadConsistency();
      document.getElementById('month').onchange = loadConsistency;
      document.getElementById('year').onchange = loadConsistency;
    });
  </script>
</body>

</html>