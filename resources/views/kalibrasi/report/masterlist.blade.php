@extends('layouts.app')

@section('styles')
    <style>
        #certificateContent iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-4">
        @if ($results->isEmpty())
            <div class="alert alert-info">No Masterlist data available.</div>
        @else
            <div class="table-responsive mb-3">
                <table class="table table-striped table-bordered align-middle text-nowrap">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>No</th>
                            <th>ID Num / SN</th>
                            <th>Equipment Name</th>
                            <th>Capacity</th>
                            <th>Accuracy</th>
                            <th>Unit</th>
                            <th>Brand</th>
                            <th>Calib. Type</th>
                            <th>1<sup>st</sup> Used</th>
                            <th>Last Calib.</th>
                            <th>Rank</th>
                            <th>Freq (bln)</th>
                            <th>Acceptance Criteria</th>
                            <th>PIC</th>
                            <th>Location</th>
                            <th>Last Certificate</th>
                            <th>Last Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $i => $item)
                            <tr class="text-center">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->id_num }} / {{ $item->sn_num }}</td>
                                <td>{{ optional($item->equipment)->name ?? '-' }}</td>
                                <td>{{ $item->capacity }}</td>
                                <td>± {{ $item->accuracy }}</td>
                                <td>{{ optional($item->unit)->symbol ?? '-' }}</td>
                                <td>{{ $item->brand }}</td>
                                <td>{{ $item->calibration_type }}</td>
                                <td>{{ $item->first_used->format('d-m-Y') }}</td>
                                @if ($item->latestResult)
                                    <td>{{ $item->latestResult->calibration_date->format('d-m-Y') }}</td>
                                @else
                                    <td>{{ $item->first_used->format('d-m-Y') }}</td>
                                @endif
                                <td>{{ $item->rank }}</td>
                                <td>{{ $item->calibration_freq }}</td>
                                <td>{{ $item->acceptance_criteria }}</td>
                                <td>{{ $item->pic }}</td>
                                <td>{{ $item->location }}</td>
                                <td>
                                    {{-- Last Certificate --}}
                                    @if ($item->latestResult && $item->latestResult->certificate)
                                        <button type="button" class="btn btn-sm btn-primary btn-view-certificate"
                                            data-bs-toggle="modal" data-bs-target="#certificateModal"
                                            data-path="{{ asset('storage/' . $item->latestResult->certificate) }}"
                                            data-ext="{{ pathinfo($item->latestResult->certificate, PATHINFO_EXTENSION) }}">
                                            Show
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Last Result --}}
                                    @if ($item->latestResult && $item->latestResult->judgement)
                                        <a class="btn btn-sm btn-primary"
                                            href="{{ route('kalibrasi.print.report.masterlist', $item->id_num) }}">
                                            {{ $item->latestResult->judgement }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div class="modal fade" id="certificateModal" tabindex="-1" aria-labelledby="certificateModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-between">
                        <h5 class="modal-title">Calibration Certificate</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printCertificate()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                    </div>
                    <div class="modal-body text-center vh-100" id="certificateContent">
                        <div class="text-muted">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div class="pagination">
                {{ $results->links() }}
            </div>
            <a href="{{ route('kalibrasi.report.menu') }}" class="btn btn-primary">Back</a>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function printCertificate() {
            const iframe = document.querySelector('#certificateContent iframe');
            if (iframe && iframe.src) {
                const printWindow = window.open(iframe.src, '_blank');
                // Tidak semua browser bisa langsung print PDF, tapi bisa diarahkan ke preview cetak
            } else {
                alert('Tidak ada sertifikat untuk dicetak.');
            }
        }
    </script>
    <script type="module">
        $(document).ready(function() {
            $('.pagination nav').addClass('w-100');
            $('.pagination nav ul').addClass('my-0 me-3');
        })
        $(document).on('click', '.btn-view-certificate', function() {
            const path = $(this).data('path');
            const ext = $(this).data('ext').toLowerCase();
            const container = $('#certificateContent');

            container.html('<div class="text-muted">Loading...</div>'); // reset dulu

            if (ext === 'pdf') {
                container.html(`<iframe loading="lazy" src="${path}"></iframe>`);
            } else {
                container.html(`<div class="text-danger">Format file tidak dikenali: .${ext}</div>`);
            }
        });
    </script>
@endsection
