@forelse ($divisions as $division)
    <tr class="text-center">
        <td>{{ $loop->iteration }}</td>
        <td class="text-start">{{ $division->name }}</td>
        <td class="text-start">{{ $division->department->name }}</td>
        <td>
            @if (in_array(auth()->user()->role, ['management', 'ypq']))
                <button class="btn btn-sm btn-outline-primary btn-edit-division" data-id="{{ $division->id }}"
                    data-name="{{ $division->name }}" data-department="{{ $division->department_id }}">
                    <i class="bi bi-pencil"></i>
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center">No result.</td>
    </tr>
@endforelse
