<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Penanganan Masalah Alat Ukur</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    <style>
        /* Pastikan hanya style yang perlu dipakai di print */
        @page {
            size: A4;
            orientation: landscape !important;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table,
        .table th,
        .table td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 4px;
            text-align: center;
        }

        thead th {
            background: #eee;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* Hide print button during printing */
        @media print {
            button.print-button {
                display: none;
            }

            button.back-button {
                display: none;
            }

            @page {
                size: A4 landscape;
                /* atau bisa pakai: size: landscape; */
                margin: 1cm;
            }

            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-0 px-1">
        <div class="row justify-content-center" style="margin-bottom: 3px;">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th scope="col" rowspan="3" class="text-center align-middle" style="width: 50px;">
                            <div class="d-grid gap-1 h-100 fs-5 fw-medium"
                                style="display: grid; justify-content: center; font-size: 10px; font-weight: 500; gap: 5px;">
                                <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" width="30px"
                                    class="mx-auto" style="margin-left: auto; margin-right: auto;">
                                PT.CAR
                            </div>
                        </th>
                        <th scope="col" colspan="7" rowspan="3" class="text-center align-middle fs-4"
                            style="font-size: 20pt;">
                            Lembar Penanganan Masalah Alat Ukur</th>
                        <th scope="col">DISIAPKAN </th>
                        <th scope="col">DIPERIKSA</th>
                        <th scope="col">DISETUJUI</th>
                    </tr>
                    <tr>
                        <td scope="col">&nbsp;</td>
                        <td scope="col">&nbsp;</td>
                        <td scope="col">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>{{ $repair->pic_repair }}</td>
                        <td>{{ $checked->name }}</td>
                        <td>{{ $approved->name }}</td>
                    </tr>
                    <tr>
                        <th scope="col" colspan="2">Equipment Name</th>
                        <td scope="col" colspan="3">{{ $repair->masterList->equipment->name }}</td>
                        <th scope="col">Capacity</th>
                        <td scope="col">{{ $repair->masterList->capacity }}
                            {{ $repair->masterList->unit->symbol ?? 'N/A' }}
                        </td>
                        <th scope="col">Repair Date</th>
                        <td scope="col" colspan="3">
                            {{ $repair->repair_date->locale('id')->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th scope="col" colspan="2">Nomor ID</th>
                        <td scope="col" colspan="3">{{ $repair->id_num }}</td>
                        <th scope="col">PIC</th>
                        <td scope="col">{{ $repair->pic_repair }}</td>
                        <th scope="col">Problem Date</th>
                        <td scope="col" colspan="3">
                            {{ $repair->problem_date->locale('id')->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th scope="col" colspan="2">Merk</th>
                        <td scope="col" colspan="3">{{ $repair->masterList->brand }}</td>
                        <th scope="col">Location</th>
                        <td scope="col">{{ $repair->masterList->location }}</td>
                        <th scope="col">Judgement</th>
                        <td scope="col" colspan="3">{{ $repair->judgement }}</td>
                    </tr>
                    <tr>
                        <th scope="col" colspan="5">Problem</th>
                        <th scope="col" colspan="6">Countermeasure
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" rowspan="6" style="height: 300px;">{{ $repair->problem }}</td>
                        <td colspan="6" rowspan="6" style="height: 300px;">{{ $repair->countermeasure }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let route = document.referrer || "{{ route('kalibrasi.report.menu') }}";
            if (route === "{{ url('/report/search') }}") {
                route = "{{ route('kalibrasi.report.menu') }}"
            }
            const printButton = document.createElement('button');
            printButton.textContent = 'Print';
            printButton.style.position = 'fixed';
            printButton.style.bottom = '20px';
            printButton.style.left = '50%';
            printButton.style.transform = 'translateX(-50%)';
            printButton.style.cursor = 'pointer';
            printButton.classList.add('print-button');
            printButton.style.padding = '10px 20px';
            printButton.style.backgroundColor = '#181d3d';
            printButton.style.color = '#fff';
            printButton.style.border = 'none';
            printButton.style.borderRadius = '5px';

            printButton.addEventListener('click', function() {
                window.print();
                setTimeout(() => {
                    window.location.href = route;
                }, 1000);
            });

            document.body.appendChild(printButton);

            const backButton = document.createElement('button');
            backButton.textContent = 'Back';
            backButton.style.position = 'fixed';
            backButton.style.bottom = '20px';
            backButton.style.left = '50%';
            backButton.style.transform = 'translateX(-180%)';
            backButton.style.cursor = 'pointer';
            backButton.classList.add('back-button');
            backButton.style.padding = '10px 20px';
            backButton.style.backgroundColor = '#181d3d';
            backButton.style.color = '#fff';
            backButton.style.border = 'none';
            backButton.style.borderRadius = '5px';

            backButton.addEventListener('click', function() {
                setTimeout(() => {
                    window.location.href = "{{ route('kalibrasi.report.menu') }}";
                }, 1000);
            });

            document.body.appendChild(backButton);
        });
    </script>
</body>

</html>
