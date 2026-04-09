<div class="table-responsive-wrapper">
    <table class="table table-sm table-hover table-bordered mb-0 table-sticky-header text-center" id="sortableTable">
        <thead class="table-primary text-center align-middle">
            <tr>
                <th scope="col">Tanggal</th>
                <th scope="col">Problem</th>
                <th scope="col">Countermeasure</th>
                <th scope="col">Remark</th>
                <th scope="col">Due Date</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody class="align-middle">
            @forelse ($Problems as $problem)
                @php
                    // 1. Cek apakah sudah lewat tenggat waktu
                    $isOverdue = $problem->due_date <= \Carbon\Carbon::now();

                    // 2. Tentukan teks dan warna default
                    $displayStatus = str_replace('_', ' ', $problem->status);
                    $badgeColor = 'bg-light text-dark';

                    // 3. Timpa teks dan warna berdasarkan kondisi status & tanggal
                    if ($problem->status === 'close') {
                        $badgeColor = 'bg-danger';
                    } elseif ($problem->status === 'open') {
                        $displayStatus = $isOverdue ? 'Delay' : 'Open';
                        $badgeColor = $isOverdue ? 'bg-warning text-dark' : 'bg-success';
                    } elseif ($problem->status === 'follow_up_1') {
                        $displayStatus = $isOverdue ? 'Follow Up 1 Delay' : 'Follow Up 1';
                        $badgeColor = $isOverdue ? 'bg-secondary' : 'bg-info text-dark';
                    }
                @endphp
                <tr data-status="{{ strtolower($problem->status) }}">
                    <td class="text-center">{{ \Carbon\Carbon::parse($problem->created_at)->format('d/m/Y') }}</td>
                    <td class="text-start">{{ $problem->problem }}</td>
                    <td class="text-start">{{ $problem->countermeasure }}</td>
                    <td class="fw-bold text-danger">{{ $problem->remark }}</td>
                    <td class="text-danger fw-bold">
                        {{ \Carbon\Carbon::parse($problem->due_date)->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="badge {{ $badgeColor }} text-uppercase">
                            {{ $displayStatus }}
                        </span>
                    </td>
                    <td>
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
                    <td colspan="7" class="text-center text-muted py-3">
                        <i class="bi bi-check2-all d-block fs-3 mb-2"></i>
                        Konsistensi Supervisor aman terjaga.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
