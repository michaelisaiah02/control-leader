<div class="table-responsive">
    <table class="mt-1 mb-0 table table-sm table-striped table-bordered text-center" id="sortableTable">
        <thead class="table-primary">
            <tr>
                <th scope="col">Tanggal</th>
                @if (auth()->user()->role !== 'leader')
                    <th scope="col">Leader</th>
                @endif
                <th scope="col">Operator</th>
                <th scope="col">Problem</th>
                <th scope="col">Countermeasure</th>
                <th scope="col">Due Date</th>
                <th scope="col">Status</th>
                @if (auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor')
                    <th scope="col">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
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
                $badgeColor    = $isOverdue ? 'bg-warning text-dark' : 'bg-success';
            } elseif ($problem->status === 'follow_up_1') {
                $displayStatus = $isOverdue ? 'Follow Up 1 Delay' : 'Follow Up 1';
                $badgeColor    = $isOverdue ? 'bg-secondary' : 'bg-info text-dark';
            }
        @endphp
                <tr data-status="{{ strtolower($problem->status) }}">
                    <td scope="col">{{ $problem->created_at->format('d/m/Y') }}</td>
                    @if (auth()->user()->role !== 'leader')
                        <th scope="col">{{ $problem->user->name }}</th>
                    @endif
                    <td scope="col">{{ $problem->inferior_id }} - {{ $problem->inferior->name }}</td>
                    <td scope="col">{{ $problem->problem }}</td>
                    <td scope="col">{{ $problem->countermeasure }}</td>
                    <td scope="col">{{ \Carbon\Carbon::parse($problem->due_date)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $badgeColor }} text-uppercase">
                            {{ $displayStatus }}
                        </span>
                    </td>
                    @if (auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor')
                        <td scope="col">
                            @if ($problem->status != 'close')
                                <a href="{{ route('listProblem.edit', ['type' => 'leader-performance', 'id' => $problem->id]) }}"
                                    class="btn btn-sm btn-outline-primary rounded-circle"><i
                                        class="bi bi-pencil-square"></i></a>
                            @else
                                -
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
