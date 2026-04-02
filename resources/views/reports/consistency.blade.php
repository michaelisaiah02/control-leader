<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <title>Monthly Consistency {{ ucfirst($type) }} Report</title>
    <style>
        @media print {

            /* 1. Setup kertas standar masa depan: A4 Landscape */
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            /* 2. Kunci flexbox biar gak turun ke bawah */
            .d-flex.justify-content-center {
                flex-wrap: nowrap !important;
                width: 100% !important;
            }

            /* 3. Kita 'tembak' semua variasi class col-8 dan col-4 biar aman sentosa */
            .col-8,
            .col-md-8,
            .col-sm-8 {
                width: 66.666667% !important;
                flex: 0 0 auto !important;
            }

            .col-4,
            .col-md-4,
            .col-sm-4 {
                width: 33.333333% !important;
                flex: 0 0 auto !important;
            }

            /* 4. Amankan ukuran chart */
            #chart {
                max-height: 300px !important;
                width: 100% !important;
            }

            /* 5. Cegah tabel terbelah dua di pergantian halaman */
            table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* 6. Paksa printer nampilin warna asli (terutama buat legend indikator merah/biru) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>
    <div class="w-100">
        <div class="d-flex w-100 justify-content-between align-items-stretch">
            <a class="border border-black d-flex flex-column text-decoration-none" style="width: 80px" href="/">
                <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" class="mt-0 mx-auto" width="50">
                <span class="text-center">
                    PT. CAR
                </span>
            </a>
            <div class="border border-black w-100 d-flex justify-content-center align-items-center">
                <p class="text-center" style="text-transform: capitalize; font-size: 2rem">Monthly {{ $type }}
                    Consistency
                    Report
                </p>
            </div>
            <a class="border border-black text-decoration-none d-flex text-center" style="width: 80px" href="/">
                <img src="{{ asset('image/logo-rice.png') }}" alt="Logo" class="my-1 mx-auto" width="60">
            </a>
        </div>
    </div>
    <div class="card p-3 shadow-sm">
        <div class="d-flex justify-content-center">
            <div class="col-md-8 col-sm-8 border border-1 border-black">
                <canvas id="chart" style="max-height: 300px;"></canvas>
            </div>
            <div class="col-md-4 border border-1 border-black d-flex flex-column justify-content-between">
                <table>
                    <tr>
                        <th>Periode</th>
                        <th>:</th>
                        <th>{{ $date->format('F Y') }}</th>
                    </tr>
                    <tr>
                        <th>Member Name</th>
                        <th>:</th>
                        <th>{{ $member->name ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <th>ID Member</th>
                        <th>:</th>
                        <th>{{ $member->employeeID ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <th>Dept</th>
                        <th>:</th>
                        <th>{{ $department->name ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <th>Bagian</th>
                        <th>:</th>
                        <th class="text-capitalize">{{ $type }}</th>
                    </tr>
                </table>
                <div class="d-flex d-print-none" style="height: 200px"></div>
                <div class="d-none d-print-flex p-1 gap-1 mt-5">
                    <table class="table table-bordered border-black mb-0 pb-0">
                        <tr>
                            <td class="py-0 my-0">Disetujui</td>
                        </tr>
                        <tr>
                            <td>
                                <div style="height: 50px"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 my-0">&nbsp;</td>
                        </tr>
                    </table>
                    <table class="table table-bordered border-black mb-0 pb-0">
                        <tr>
                            <td class="py-0 my-0">Diperiksa</td>
                        </tr>
                        <tr>
                            <td>
                                <div style="height: 50px"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 my-0">&nbsp;</td>
                        </tr>
                    </table>
                    <table class="table table-bordered border-black mb-0 pb-0">
                        <tr>
                            <td class="py-0 my-0">Dibuat</td>
                        </tr>
                        <tr>
                            <td>
                                <div style="height: 50px"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 my-0">&nbsp;</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="row my-3">
            <table class="table table-sm table-bordered border-black align-middle">
                <thead>
                    <tr class="align-middle">
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
                            <td class="text-center">{{ $problem->created_at->format('d M Y') }}</td>
                            <td>{{ $problem->problem }}</td>
                            <td>{{ $problem->countermeasure }}</td>
                            <td class="text-center">
                                {{ $problem->due_date ? $problem->due_date->format('d M Y') : '-' }}</td>
                            <td class="text-center text-capitalize">{{ str_replace('_', ' ', $problem->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted fw-bold">
                                Konsistensi sempurna! Tidak ada problem pada periode ini. ✅
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-3 justify-content-end align-items-center d-print-none">
            <a href="{{ route('reports.form', ['type' => $type]) }}"
                class="btn btn-primary text-white py-2 px-4">Back</a>
            <button onclick="window.print()" class="btn btn-primary py-2 px-4">Print</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('chart');

            let apiUrl = '';
            const monthParam = `month={{ $date->format('Y-m') }}`;

            @if ($type === 'supervisor')
                apiUrl =
                    `/reports/api/supervisor-consistency?${monthParam}&supervisor_id={{ request('supervisor') }}`;
            @elseif ($type === 'leader')
                apiUrl = `/reports/api/leader-consistency?${monthParam}&leader_id={{ request('leader') }}`;
            @endif

            if (apiUrl !== '') {
                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        new Chart(ctx, {
                            data: {
                                labels: data.labels,
                                datasets: data.datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    // 🔥 BIKIN STACKED KHUSUS LEADER 🔥
                                    x: {
                                        stacked: {{ $type === 'leader' ? 'true' : 'false' }}
                                    },
                                    y: {
                                        stacked: {{ $type === 'leader' ? 'true' : 'false' }},
                                        beginAtZero: true,
                                        max: 100,
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            // Trik manipulasi icon legend biar nggak ikut menebal
                                            generateLabels: function(chart) {
                                                const originalLabels = Chart.defaults.plugins.legend
                                                    .labels.generateLabels(chart);
                                                return originalLabels.map(label => {
                                                    if (label.text === 'Target') {
                                                        // Paksa ketebalan garis di icon legend jadi 0
                                                        // Biar dia balik jadi kotak solid normal kayak yang lain
                                                        label.lineWidth = 0;
                                                    }
                                                    return label;
                                                });
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed
                                                    .y + '%';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error("Gagal load data chart:", error);
                        ctx.parentElement.innerHTML =
                            '<p class="text-center text-danger mt-5">Gagal memuat grafik</p>';
                    });
            }
        });
    </script>
</body>

</html>
