<table>
    <thead>
        <tr>
            <th>No</th>
            <th>ID Num / SN</th>
            <th>Equipment Name</th>
            <th>Capacity</th>
            <th>Accuracy</th>
            <th>Unit</th>
            <th>Brand</th>
            <th>Calib. Type</th>
            <th>1<sup>st</sup> Used</th>
            <th>Last Calib.</th>
            <th>Rank</th>
            <th>Freq (bln)</th>
            <th>Acceptance Criteria</th>
            <th>PIC</th>
            <th>Location</th>
            <th>Last Result</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $i => $row)
            <tr class="text-center">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->id_num }} / {{ $row->sn_num }}</td>
                <td>{{ optional($row->equipment)->name }}</td>
                <td>{{ $row->capacity }}</td>
                <td>{{ $row->accuracy }}</td>
                <td>
                    @if ($row->unit)
                        {{ $row->unit->symbol }}
                    @else
                        <span class="text-danger">N/A</span>
                    @endif
                </td>
                <td>{{ $row->brand }}</td>
                <td>{{ $row->calibration_type }}</td>
                <td>{{ $row->first_used->format('d-m-Y') }}</td>
                @if ($row->latestResult)
                    <td>{{ $row->latestResult->calibration_date->format('d-m-Y') }}</td>
                @else
                    <td>{{ $row->first_used->format('d-m-Y') }}</td>
                @endif
                <td>{{ $row->rank }}</td>
                <td>{{ $row->calibration_freq }}</td>
                <td>{{ $row->acceptance_criteria }}</td>
                <td>{{ $row->pic }}</td>
                <td>{{ $row->location }}</td>
                <td>
                    {{-- Last Result --}}
                    @if ($row->latestResult && $row->latestResult->judgement)
                        {{ $row->latestResult->judgement }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $row->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
