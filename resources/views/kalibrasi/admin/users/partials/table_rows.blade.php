@forelse ($users as $user)
    <tr class="text-center">
        <td>{{ $user->employeeID }}</td>
        <td class="text-start">{{ $user->name }}</td>
        <td>
            @if ($user->role === 'admin')
                <i class="bi bi-person-gear"></i> Admin
            @elseif ($user->role === 'user')
                <i class="bi bi-person"></i> User
            @else
                <i class="bi bi-person-badge"></i> Guest
            @endif
        </td>
        <td>{{ $user->created_at->format('j F Y H:i') }}</td>
        <td>
            @if ($user->approved)
                <i class="bi bi-check-lg text-success"></i>
            @else
            @endif
        </td>
        <td>
            @if ($user->checked)
                <i class="bi bi-check-lg text-success"></i>
            @else
            @endif
        </td>
        <td>
            @if (Auth::user()->role === 'admin')
                <button class="btn btn-sm btn-primary btn-edit-user" data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}" data-employeeid="{{ $user->employeeID }}"
                    data-role="{{ $user->role }}" data-approved="{{ $user->approved }}"
                    data-checked="{{ $user->checked }}">
                    Edit
                </button>
                <button class="btn btn-sm btn-danger btn-delete-user" data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                    Delete
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">No result.</td>
    </tr>
@endforelse
