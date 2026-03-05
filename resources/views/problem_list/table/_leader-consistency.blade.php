@php
    $loggedInRole = auth()->user()->role;
    $isLeader = $loggedInRole === 'leader';
@endphp

<div class="table-responsive">
    <table class="table table-sm table-striped table-bordered mt-1 mb-0" id="sortableTable">
        <thead class="table-primary text-center align-middle">
            <tr>
                <th scope="col">Tanggal</th>

                {{-- Sembunyikan kolom Leader kalau yang login Leader --}}
                @if (!$isLeader)
                    <th scope="col">Leader</th>
                @endif

                <th scope="col">Operator</th>
                <th scope="col">Problem</th>
                <th scope="col">Countermeasure</th>
                <th scope="col">Remark</th>
                <th scope="col">Due Date</th>
                <th scope="col">Status</th>
                @if (auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor')
                    <th scope="col">Action</th>
                @endif
            </tr>
        </thead>
        <tbody class="align-middle">
            @forelse ($Problems as $problem)
                <tr data-status="{{ strtolower($problem->status) }}">
                    <td class="text-center">{{ \Carbon\Carbon::parse($problem->created_at)->format('d/m/Y') }}</td>

                    @if (!$isLeader)
                        <td>{{ $problem->user->name ?? '-' }}</td>
                    @endif

                    <td>{{ $problem->inferior->employeeID ?? '' }} - {{ $problem->inferior->name ?? '-' }}</td>
                    <td>{{ $problem->problem }}</td>
                    <td>{{ $problem->countermeasure }}</td>
                    <td class="text-center fw-bold text-danger">{{ $problem->remark }}</td>
                    <td class="text-center text-danger fw-bold">
                        {{ \Carbon\Carbon::parse($problem->due_date)->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        @php
                            $badgeColor = match ($problem->status) {
                                'open' => 'bg-success',
                                'close' => 'bg-danger',
                                'delay' => 'bg-warning text-dark',
                                'follow_up_1' => 'bg-info text-dark',
                                'follow_up_1_delay' => 'bg-secondary',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <span class="badge {{ $badgeColor }} text-uppercase my-0">
                            {{ str_replace('_', ' ', $problem->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if ($problem->status != 'close')
                            <a href="{{ route('listProblem.edit', ['type' => $type, 'id' => $problem->id]) }}"
                                class="btn btn-sm btn-outline-primary rounded-circle" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        @else
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isLeader ? '8' : '9' }}" class="text-center text-muted py-3">
                        <i class="bi bi-check2-all d-block fs-3 mb-2"></i>
                        Semua jadwal aman, tidak ada problem konsistensi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
