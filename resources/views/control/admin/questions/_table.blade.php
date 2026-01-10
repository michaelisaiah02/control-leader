<div class="table-responsive">
    <table class="mt-1 mb-0 table table-sm table-striped table-bordered" id="sortableTable">
        <thead class="table-primary text-center">
            <tr>
                <th scope="col">No</th>
                <th scope="col">Pertanyaan</th>
                <th scope="col">Type</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody id="sortableBody">
            @forelse($questions as $question)
            <tr data-id="{{ $question->id }}">
                <!-- <td scope="col" class="handle cursor-grab">{{ $question->display_order }}</td> -->
                <td scope="col" class="handle cursor-grab text-center">
                    <!-- {{ ($questions->currentPage() - 1) * $questions->perPage() + $loop->iteration }} -->
                    {{ $question->display_order }}
                </td>
                <td>{{ $question->question_text }}</td>
                <td>
                    @switch($question->package)
                    @case('awal_shift')
                    Awal Shift
                    @break

                    @case('saat_bekerja')
                    Saat Bekerja
                    @break

                    @case('setelah_istirahat')
                    Setelah Istirahat
                    @break

                    @case('akhir_shift')
                    Akhir Shift
                    @break

                    @case('leader')
                    Leader
                    @break

                    @default
                    Tidak Teridenfikasi
                    @break
                    @endswitch
                </td>
                <td class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('control.admin.question.edit', $question->id) }}"
                        class="btn btn-primary btn-sm">Edit</a>
                    <form method="POST" action="{{ route('control.admin.question.destroy', $question->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Apakah yakin dihapus?')"
                            class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-3 text-muted">Tidak ada data pertanyaan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">
    {{ $questions->links() }}
</div>