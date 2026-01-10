@forelse ($masterlists as $masterlist)
    <tr class="text-center">
        <td>{{ $masterlist->id_num }}</td>
        <td>{{ $masterlist->sn_num }}</td>
        <td>{{ $masterlist->capacity }}
            @if ($masterlist->unit)
                {{ $masterlist->unit->symbol }}
            @else
                <span class="text-danger">N/A</span>
            @endif

        </td>
        <td>{{ $masterlist->accuracy }}
            @if ($masterlist->unit)
                {{ $masterlist->unit->symbol }}
            @else
                <span class="text-danger">N/A</span>
            @endif

        </td>
        <td>{{ $masterlist->brand }}</td>
        <td>{{ $masterlist->calibration_type }}</td>
        <td>{{ $masterlist->first_used->format('d F Y') }}</td>
        <td>{{ $masterlist->rank }}</td>
        <td>{{ $masterlist->calibration_freq }}</td>
        <td>{{ $masterlist->acceptance_criteria }}</td>
        <td>{{ $masterlist->pic }}</td>
        <td>{{ $masterlist->location }}</td>
        <td>
            <button class="btn btn-primary btn-sm btn-edit-masterlist" data-id="{{ $masterlist->id }}"
                data-id-num="{{ $masterlist->id_num }}" data-sn-num="{{ $masterlist->sn_num }}"
                data-id-unit="{{ optional($masterlist->unit)->id ?? 'N/A' }}"
                data-capacity="{{ $masterlist->capacity }}" data-accuracy="{{ $masterlist->accuracy }}"
                data-brand="{{ $masterlist->brand }}" data-calibration-type="{{ $masterlist->calibration_type }}"
                data-first-used="{{ $masterlist->first_used->format('Y-m-d') }}" data-rank="{{ $masterlist->rank }}"
                data-calibration-freq="{{ $masterlist->calibration_freq }}"
                data-acceptance-criteria="{{ $masterlist->acceptance_criteria }}" data-pic="{{ $masterlist->pic }}"
                data-location="{{ $masterlist->location }}">
                Edit
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="15" class="text-center">No data available</td>
    </tr>
@endforelse
