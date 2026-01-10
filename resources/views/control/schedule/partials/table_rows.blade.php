@forelse ($users as $operator)
    <tr class="text-center">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $operator->employeeID }}</td>
        <td class="text-start">{{ $operator->name }}</td>
        <td>{{ $operator->division->division_name }}</td>
        <td>
            <button class="btn btn-sm btn-primary btn-edit-operator" data-id="{{ $operator->id }}"
                data-name="{{ $operator->name }}" data-employeeid="{{ $operator->employeeID }}"
                data-division="{{ $operator->division->id }}" data-leader="{{ $operator->superior_id }}">
                Edit
            </button>
            <button class="btn btn-sm btn-danger btn-delete-operator" data-id="{{ $operator->id }}"
                data-name="{{ $operator->name }}" data-bs-toggle="modal" data-bs-target="#deleteOperatorModal">
                Delete
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">No result.</td>
    </tr>
@endforelse
