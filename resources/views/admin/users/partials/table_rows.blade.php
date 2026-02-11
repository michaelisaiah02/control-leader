@forelse ($users as $user)
    <tr class="text-center">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $user->employeeID }}</td>
        <td class="text-start">{{ $user->name }}</td>
        <td>
            @switch($user->role)
                @case('management')
                    <i class="bi bi-person-badge-fill"></i> Management
                @break

                @case('ypq')
                    <i class="bi bi-person-rolodex"></i> YPQ Team
                @break

                @case('admin')
                    <i class="bi bi-person-gear"></i> Admin
                @break

                @case('supervisor')
                    <i class="bi bi-person-up"></i> Supervisor
                @break

                @case('leader')
                    <i class="bi bi-person-lines-fill"></i> Leader
                @break

                @default
                    <i class="bi bi-person-badge"></i> Guest
            @endswitch
        </td>
        <td>{{ $user->superior?->employeeID ?? '-' }}</td>
        <td>{{ $user->superior?->name ?? '-' }}</td>
        <td>{{ $user->department?->name ?? '-' }}</td>
        <td>
            @if (in_array(auth()->user()->role, ['admin', 'management', 'ypq']))
                <button class="btn btn-sm btn-outline-primary btn-edit-user" data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}" data-employeeid="{{ $user->employeeID }}"
                    data-role="{{ $user->role }}" data-department="{{ $user->department_id }}"
                    data-superior="{{ $user->superior_id }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger btn-delete-user" data-id="{{ $user->id }}"
                    data-name="{{ $user->name }}" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                    <i class="bi bi-trash"></i>
                </button>
            @endif
        </td>
    </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">No result.</td>
        </tr>
    @endforelse
