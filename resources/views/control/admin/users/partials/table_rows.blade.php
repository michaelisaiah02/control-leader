@forelse ($users as $user)
    <tr class="text-center">
        <td>{{ $user->employeeID }}</td>
        <td class="text-start">{{ $user->name }}</td>
        <td>
            @switch($user->role)
                @case('admin')
                    <i class="bi bi-person-gear"></i> Admin
                @break

                @case('supervisor')
                    <i class="bi bi-person-up"></i> Supervisor
                @break

                @case('leader')
                    <i class="bi bi-person-lines-fill"></i> Leader
                @break

                @case('operator')
                    <i class="bi bi-person"></i> Operator
                @break

                @default
                    <i class="bi bi-person-badge"></i> Guest
            @endswitch
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
            @if (auth()->guard('web_control_leader')->user()->role === 'admin')
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
