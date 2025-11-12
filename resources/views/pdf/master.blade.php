<!DOCTYPE html>
<html>

<head>
    <title>Laporan Master List</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* biar kolom rata */
            word-wrap: break-word;
            /* biar teks panjang gak keluar kolom */
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
        }

        h3 {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h3>Daftar Master List</h3>
    <table>
        <thead>
            <tr>
                <th>ID Num</th>
                <th>SN Num</th>
                <th>Capacity</th>
                <th>Accuracy</th>
                <th>Brand</th>
                <th>Calibration Type</th>
                <th>1st Used</th>
                <th>Rank</th>
                <th>Calibration Freq</th>
                <th>Acc Criteria</th>
                <th>PIC</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $row)
                <tr class="text-center">
                    <td>{{ $row->id_num }}</td>
                    <td>{{ $row->sn_num }}</td>
                    <td>{{ $row->capacity }}
                        @if ($row->unit)
                            {{ $row->unit->symbol }}
                        @else
                            <span class="text-danger">N/A</span>
                        @endif

                    </td>
                    <td>{{ $row->accuracy }}
                        @if ($row->unit)
                            {{ $row->unit->symbol }}
                        @else
                            <span class="text-danger">N/A</span>
                        @endif

                    </td>
                    <td>{{ $row->brand }}</td>
                    <td>{{ $row->calibration_type }}</td>
                    <td>{{ $row->first_used->format('d F Y') }}</td>
                    <td>{{ $row->rank }}</td>
                    <td>{{ $row->calibration_freq }}</td>
                    <td>{{ $row->acceptance_criteria }}</td>
                    <td>{{ $row->pic }}</td>
                    <td>{{ $row->location }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
