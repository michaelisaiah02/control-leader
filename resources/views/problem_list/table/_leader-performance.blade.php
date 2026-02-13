<div class="table-responsive">
    <table class="mt-1 mb-0 table table-sm table-striped table-bordered" id="sortableTable">
        <thead class="table-primary text-center">
            <tr>
                <th scope="col">Tanggal</th>
                <th scope="col">Leader</th>
                <th scope="col">Operator</th>
                <th scope="col">Problem</th>
                <th scope="col">Countermeasure</th>
                <th scope="col">Due Date</th>
                <th scope="col">Status</th>
                @if(auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor')
                <th scope="col">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($Problems as $problem)
            <tr class="text-center">
                <th scope="col">{{ $problem->created_at->format("d/m/Y") }}</th>
                <th scope="col">{{ $problem->leader_name }}</th>
                <th scope="col">{{ $problem->operator_id }} - {{ $problem->operator_name }}</th>
                <th scope="col">{{ $problem->problem }}</th>
                <th scope="col">{{ $problem->countermeasure}}</th>
                <th scope="col">{{ $problem->due_date }}</th>
                <th scope="col" class="capitalize">{{ $problem->status }}</th>
                @if(auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor')
                <th scope="col">
                    @if ($problem->status != 'close')
                    <a href="{{ route('listProblem.edit', ['type' => 'leader-performance', 'id' => $problem->id]) }}">Edit</a>
                    @else
                    -
                    @endif
                </th>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>