@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        @if ($repairs->isEmpty())
            <div class="alert alert-info">No Repair data available.</div>
        @else
            <div class="table-responsive mb-3">
                <table class="table table-striped table-bordered align-middle text-nowrap">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>No</th>
                            <th>Problem Date</th>
                            <th>Repair Date</th>
                            <th>ID Num / SN</th>
                            <th>Equipment Name</th>
                            <th>Problem</th>
                            <th>Countermeasure</th>
                            <th>Judgement</th>
                            <th>PIC</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($repairs as $repair)
                            <tr class="text-center">
                                <td><a class="btn btn-primary btn-select-id"
                                        href="{{ route('kalibrasi.print.report.repair', $repair->id_num) }}{!! '?return_url=' . urlencode(url()->full()) !!}">{{ $loop->iteration }}</a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($repair->problem_date)->format('d-m-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($repair->repair_date)->format('d-m-Y') }}</td>
                                <td>{{ $repair->id_num }} / {{ optional($repair->masterList)->sn_num ?? '-' }}</td>
                                <td>{{ optional($repair->masterList->equipment)->name ?? '-' }}</td>
                                <td>{{ $repair->problem }}</td>
                                <td>{{ $repair->countermeasure }}</td>
                                <td>{{ $repair->judgement }}</td>
                                <td>{{ $repair->pic_repair }}</td>
                                <td>{{ $repair->masterList->location }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center">
            <div class="pagination w-100">
                {{ $repairs->links() }}
            </div>
            <a href="{{ route('kalibrasi.report.menu') }}" class="btn btn-primary">Back</a>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            $('.pagination nav').addClass('w-100');
            $('.pagination nav ul').addClass('my-0 me-3');
        })
    </script>
@endsection
