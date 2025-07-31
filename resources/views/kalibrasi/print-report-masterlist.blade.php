<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Print Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    {{-- @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js']) --}}
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
                        <th scope="col" colspan="1" rowspan="3" class="text-center align-middle"
                            style="width: 50px;">
                            <div class="d-grid gap-1 h-100 fs-5 fw-medium"
                                style="display: grid; justify-content: center; font-size: 10px; font-weight: 500; gap: 5px;">
                                <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" width="30px"
                                    class="mx-auto" style="margin-left: auto; margin-right: auto;">
                                PT.CAR
                            </div>
                        </th>
                        <th scope="col" colspan="5" rowspan="3" class="text-center align-middle fs-4"
                            style="font-size: 20pt;">
                            VERIFIKASI HASIL KALIBRASI</th>
                        <th scope="col">NO.FORM</th>
                        <th scope="col">:</th>
                        <th scope="col">FR-QAC-098</th>
                    </tr>
                    <tr>
                        <th scope="col">TGL.FORM</th>
                        <th scope="col">:</th>
                        <th scope="col">11/01/2023</th>
                    </tr>
                    <tr>
                        <th scope="col">Revisi</th>
                        <th scope="col">:</th>
                        <th scope="col">0</th>
                    </tr>
                    <tr>
                        <th scope="col" colspan="2">Equipment Name</th>
                        <th scope="col" colspan="4">{{ $result->masterList->equipment->name }}</th>
                        <th scope="col">Merk</th>
                        <th scope="col">:</th>
                        <th scope="col">{{ $result->masterList->brand }}</th>
                    </tr>
                    <tr>
                        <th scope="col" colspan="2">No. ID</th>
                        <th scope="col" colspan="4">{{ $result->id_num }}</th>
                        <th scope="col">Cal Equipment</th>
                        <th scope="col">:</th>
                        <th scope="col">{{ $result->calibrator_equipment ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <th scope="col" colspan="2">Calibration Date</th>
                        <th scope="col" colspan="4">
                            {{ $result->calibration_date->locale('id')->translatedFormat('d F Y') }}</th>
                        <th scope="col">Std Keberterimaan</th>
                        <th scope="col">:</th>
                        <th scope="col">{{ $result->masterList->acceptance_criteria }}</th>
                    </tr>
                    <tr>
                        <th scope="col" style="width: 15px;">No</th>
                        <th scope="col" colspan="2">Standard Indication
                            ({{ optional($result->masterList->unit)->symbol ?? 'N/A' }})
                        </th>
                        <th scope="col" colspan="2">Actual Indication
                            ({{ optional($result->masterList->unit)->symbol ?? 'N/A' }})
                        </th>
                        <th scope="col">Correction ({{ optional($result->masterList->unit)->symbol ?? 'N/A' }})</th>
                        <th scope="col" colspan="3">Judgment</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 10; $i++)
                        @php
                            $key = 'param_' . str_pad($i, 2, '0', STR_PAD_LEFT);
                            $std = $result->masterList->standard->$key;
                            $val = $result->$key;
                        @endphp
                        <tr>
                            <th scope="row">{{ $i }}</th>
                            @if ($std == 99999)
                                <td colspan="2">-</td>
                                <td colspan="2">-</td>
                                <td>-</td>
                            @else
                                <td colspan="2">{{ $std }}</td>
                                <td colspan="2">{{ $val }}</td>
                                <td>{{ $val - $std }}</td>
                            @endif

                            @if ($i == 1)
                                <td colspan="3" rowspan="10">{{ $result->judgement }}</td>
                            @endif
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="row justify-content-end" style="display: flex; justify-content: end;">
            <div class="col-4" style="width: 40%;">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 33%;">Disetujui</th>
                            <th scope="col" style="width: 33%;">Diperiksa</th>
                            <th scope="col" style="width: 33%;">Dibuat</th>
                        </tr>
                        <tr>
                            <th scope="col" style="height: 50px">&nbsp;</th>
                            <th scope="col" style="height: 50px">&nbsp;</th>
                            <th scope="col" style="height: 50px">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $approved->name }}</td>
                            <td>{{ $checked->name }}</td>
                            <td>{{ $result->creator->name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    window.location.href = "{{ route('kalibrasi.report.menu') }}";
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
