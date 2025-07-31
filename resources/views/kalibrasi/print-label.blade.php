<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Label Kalibrasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        @font-face {
            font-family: 'Free3of9';
            src: url('/fonts/Free3of9.woff') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page {
            size: 3.5cm 0.98cm;
            margin: 0;
        }

        body {
            width: 3.5cm;
            height: 0.93cm;
            font-family: Arial, Helvetica, sans-serif;
            border: 2px solid black;
            box-sizing: border-box;
            font-size: 5.8pt;
            transform: translateY(0.7px);
        }

        .barcode {
            font-family: 'Free3of9', monospace;
            font-size: 16pt;
            margin: 0;
            padding: 0;
            line-height: 0.60;
            vertical-align: text-top;
            text-align: center;
        }


        @media print {
            @page {
                size: 3.5cm 0.98cm;
            }

            body {
                width: 3.5cm;
                height: 0.93cm;
                font-family: Arial, Helvetica, sans-serif;
                border: 2px solid black;
                box-sizing: border-box;
                font-size: 5.8pt;
                transform: translateY(0.7px);
                line-height: 0;
            }

            .barcode {
                font-family: 'Free3of9', monospace;
                font-size: 16pt;
                margin: 0;
                padding: 0;
                line-height: 0.6;
                vertical-align: text-top;
                text-align: center;
            }

            .print-wrapper {
                width: 100%;
                max-width: 3.5cm;
                transform: translateY(-2px);
                line-height: 1.5;
            }
        }
    </style>
</head>

<body>
    <div class="print-wrapper">
        <div style="display: flex; justify-content: center; padding-right: 0; padding-left: 0; column-gap: 2px;">
            <div class="col-auto">
                <strong>Valid:
                    @if ($equipment->results->count() > 0)
                        {{ $equipment->results->last()->calibration_date->format('d-m-Y') }}
                    @else
                        {{ $equipment->first_used->format('d-m-Y') }}
                    @endif
                </strong>
            </div>
            <div class="col-auto">
                <strong>Until:
                    @if ($equipment->results->count() > 0)
                        {{ $equipment->results->last()->calibration_date->addMonth($equipment->calibration_freq)->format('d-m-Y') }}
                    @else
                        {{ $equipment->first_used->addMonth($equipment->calibration_freq)->format('d-m-Y') }}
                    @endif
                </strong>
            </div>
        </div>
        <p class="barcode row justify-content-center">
            {{ '*' . $equipment->id_num . '*' }}
        </p>
        <p class="text-center" style="justify-content: center; font-weight: bold;">
            {{ $equipment->id_num }}</p>
    </div>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(() => {
                window.location.href =
                    "{{ route('dashboard', ['key' => 'menu-input', 'application' => 'KALIBRASI']) }}";
            }, 1000); // Delay biar gak ke-redirect sebelum sempat print
        }
    </script>
</body>

</html>
