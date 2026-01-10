<div class="table-responsive">
    <table class="mt-1 mb-0 table table-sm table-striped table-bordered" id="sortableTable">
        <thead class="table-primary text-center">
            <tr>
                <th scope="col">Tanggal</th>
                <th scope="col">Leader</th>
                <th scope="col">Problem</th>
                <th scope="col">Countermeasure</th>
                <th scope="col">Due Date</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($Problems as $problem)
            <tr>
                <th scope="col">{{ $problem->created_at }}</th>
                <th scope="col">{{ $problem->leader_name }}</th>
                <th scope="col">{{ $problem->problem }}</th>
                <th scope="col">{{ $problem->countermeasure}}</th>
                <th scope="col">{{ $problem->due_date }}</th>
                <th scope="col">{{ $problem->status }}</th>
                <th scope="col">Action</th>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>