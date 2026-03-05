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
                <tr data-status="{{ strtolower($problem->status) }}">
                    <th scope="col">{{ $problem->created_at->format('d/m/Y') }}</th>
                    @if (auth()->user()->role !== 'leader')
                        <th scope="col">{{ $problem->user->name }}</th>
                    @endif
                    <th scope="col">{{ $problem->inferior_id }} - {{ $problem->inferior->name }}</th>
                    <th scope="col">{{ $problem->problem }}</th>
                    <th scope="col">{{ $problem->countermeasure }}</th>
                    <th scope="col">{{ \Carbon\Carbon::parse($problem->due_date)->format('d/m/Y') }}</th>
                    <th>
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
                        <span class="badge {{ $badgeColor }} text-uppercase">
                            {{ str_replace('_', ' ', $problem->status) }}
                        </span>
                    </th>
                    @if (auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor')
                        <th scope="col">
                            @if ($problem->status != 'close')
                                <a href="{{ route('listProblem.edit', ['type' => 'leader-performance', 'id' => $problem->id]) }}"
                                    class="btn btn-sm btn-outline-primary rounded-circle"><i
                                        class="bi bi-pencil-square"></i></a>
                            @else
                                -
                            @endif
                        </th>
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
