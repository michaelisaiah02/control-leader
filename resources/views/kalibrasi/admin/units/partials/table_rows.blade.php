@forelse ($units as $unit)
    <tr class="text-center">
        <td>{{ $unit->symbol }}</td>
        <td>{{ $unit->name }}</td>
        <td>
            <button class="btn btn-primary btn-sm btn-edit-unit" data-id="{{ $unit->id }}"
                data-symbol="{{ $unit->symbol }}" data-name="{{ $unit->name }}">
                Edit
            </button>
            <button class="btn btn-danger btn-sm btn-delete-unit" data-id="{{ $unit->id }}"
                data-symbol="{{ $unit->symbol }}" data-name="{{ $unit->name }}" data-bs-toggle="modal"
                data-bs-target="#deleteUnitModal">
                Delete
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="15" class="text-center">No data available</td>
    </tr>
@endforelse
