@forelse ($standards as $standard)
    <tr class="text-center">
        <td>{{ $standard->id_num }}</td>
        <td>{{ $standard->masterList->equipment->name }}</td>
        <td>{{ $standard->masterList->capacity }}
            @isset($standard->masterList->unit->symbol)
                {{ $standard->masterList->unit->symbol }}
            @else
                <span class="text-danger">N/A</span>
            @endisset
        </td>
        <td>{{ $standard->masterList->accuracy }}
            @isset($standard->masterList->unit->symbol)
                {{ $standard->masterList->unit->symbol }}
            @else
                <span class="text-danger">N/A</span>
            @endisset
        </td>
        <td>{{ $standard->param_01 == 99999 ? '-' : floatval($standard->param_01) }}</td>
        <td>{{ $standard->param_02 == 99999 ? '-' : floatval($standard->param_02) }}</td>
        <td>{{ $standard->param_03 == 99999 ? '-' : floatval($standard->param_03) }}</td>
        <td>{{ $standard->param_04 == 99999 ? '-' : floatval($standard->param_04) }}</td>
        <td>{{ $standard->param_05 == 99999 ? '-' : floatval($standard->param_05) }}</td>
        <td>{{ $standard->param_06 == 99999 ? '-' : floatval($standard->param_06) }}</td>
        <td>{{ $standard->param_07 == 99999 ? '-' : floatval($standard->param_07) }}</td>
        <td>{{ $standard->param_08 == 99999 ? '-' : floatval($standard->param_08) }}</td>
        <td>{{ $standard->param_09 == 99999 ? '-' : floatval($standard->param_09) }}</td>
        <td>{{ $standard->param_10 == 99999 ? '-' : floatval($standard->param_10) }}</td>
        <td>
            <button class="btn btn-primary btn-sm btn-edit-standard" data-id="{{ $standard->id }}"
                data-id-num="{{ $standard->id_num }}" data-param01="{{ floatval($standard->param_01) }}"
                data-param02="{{ floatval($standard->param_02) }}" data-param03="{{ floatval($standard->param_03) }}"
                data-param04="{{ floatval($standard->param_04) }}" data-param05="{{ floatval($standard->param_05) }}"
                data-param06="{{ floatval($standard->param_06) }}" data-param07="{{ floatval($standard->param_07) }}"
                data-param08="{{ floatval($standard->param_08) }}" data-param09="{{ floatval($standard->param_09) }}"
                data-param10="{{ floatval($standard->param_10) }}">
                Edit
            </button>
            <button class="btn btn-danger btn-sm btn-delete-standard" data-id="{{ $standard->id }}"
                data-id-num="{{ $standard->masterList->id_num }}"
                data-equipment="{{ $standard->masterList->equipment->name }}" data-bs-toggle="modal"
                data-bs-target="#deleteStandardModal">
                Delete
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="15" class="text-center">No data available</td>
    </tr>
@endforelse
