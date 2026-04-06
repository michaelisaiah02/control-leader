<div class="table-responsive-wrapper">

    <table class="table table-sm table-hover table-striped mb-0 table-sticky-header" id="sortableTable">
        <thead class="text-secondary small text-uppercase fw-bold">
            <tr>
                <th class="py-2 ps-3" width="5%">No</th>
                <th class="py-2 text-center" width="5%">Sort</th>
                <th class="py-2 text-center" width="10%">Order</th>
                <th class="py-2" width="45%">Question</th>
                <th class="py-2 text-center" width="15%">Type</th>
                <th class="py-2 text-end pe-3" width="15%">Action</th>
            </tr>
        </thead>

        {{-- UPDATE 1: Tambahin data-start-index biar pagination aman --}}
        <tbody id="sortableBody" class="small align-middle">
            @forelse($questions as $question)
                @php
                    $isSortable = request()->has('package') && request('package') != '';
                @endphp

                <tr data-id="{{ $question->id }}" class="{{ $isSortable ? 'drag-handle cursor-grab' : '' }}">

                    {{-- UPDATE 2: Bungkus $pageNumber pake span class="row-number" --}}
                    <td class="ps-3 fw-bold text-secondary">
                        <span class="row-number">{{ $loop->iteration }}</span>
                    </td>

                    {{-- Kolom Drag Handle --}}
                    <td class="text-center">
                        @if ($isSortable)
                            <i class="bi bi-grip-vertical fs-5" title="Drag to reorder"></i>
                        @else
                            <span class="text-muted opacity-25">&bullet;</span>
                        @endif
                    </td>

                    {{-- Kolom Display Order (Ini yang lo pake buat debugging di screenshot) --}}
                    <td class="text-center fw-bold text-primary display-order-val">
                        {{ $question->display_order }}
                    </td>

                    <td>
                        <div class="text-truncate-2" style="max-width: 400px;">
                            {{ $question->question_text }}
                        </div>
                    </td>

                    <td class="text-center">
                        @php
                            $badgeClass = match ($question->package) {
                                'leader' => 'bg-info text-dark',
                                'awal_shift' => 'bg-success',
                                'akhir_shift' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} rounded-pill border border-light shadow-sm">
                            {{ Str::headline($question->package) }}
                        </span>
                    </td>

                    <td class="text-end pe-3">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('question.edit', $question->id) }}"
                                class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <button id="deleteBtn" type="button" class="btn btn-sm btn-outline-danger border-0" data-bs-toggle="modal" data-bs-target="#deleteModal" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center opacity-50">
                            <i class="bi bi-clipboard-x display-4 mb-2"></i>
                            <p class="mb-0 fw-bold">No questions found</p>
                            <small>Select a filter package or add a new question.</small>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
