<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <div id="chart"></div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      async function loadData() {
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        const res = await fetch(`/api/reports/daily?month=${month}&year=${year}`);
        const data = await res.json();

        chart.data.labels = json.labels;
        chart.data.datasets[0].data = json.scores;
        chart.update();
      }

      const chart = new Chart(document.getElementById('chart').getContext('2d'), {
        type: 'bar',
        data: {
          labels: [],
          datasets: [{
            label: 'Daily Score (%)',
            data: [],
            borderWidth: 2,
            backgroundColor: 'rgba(54,162,235,0.5)',
            borderColor: 'rgb(54,162,235)'
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
      });

      loadData();
      document.getElementById('month').addEventListener('change', loadData);
      document.getElementById('year').addEventListener('change', loadData);
    });
  </script>
</body>

</html>