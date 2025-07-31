@forelse ($equipments as $equipment)
    <tr class="text-center">
        <td>{{ $equipment->type_id }}</td>
        <td>{{ $equipment->name }}</td>
        <td>
            <button class="btn btn-primary btn-sm btn-edit-equipment" data-id="{{ $equipment->id }}"
                data-type-id="{{ $equipment->type_id }}" data-name="{{ $equipment->name }}">
                Edit
            </button>
            <button class="btn btn-danger btn-sm btn-delete-equipment" data-id="{{ $equipment->id }}"
                data-type-id="{{ $equipment->type_id }}" data-name="{{ $equipment->name }}" data-bs-toggle="modal"
                data-bs-target="#deleteEquipmentModal">
                Delete
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="15" class="text-center">No data available</td>
    </tr>
@endforelse
