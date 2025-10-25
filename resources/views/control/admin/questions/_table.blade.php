<table class="my-2 table table-striped table-bordered" id="sortableTable">
  <thead class="table-primary">
    <tr>
      <th scope="col">No</th>
      <th scope="col">Pertanyaan</th>
      <th scope="col">Type</th>
      <th scope="col" class="text-center">Date Created</th>
      <th scope="col" class="text-center">Action</th>
    </tr>
  </thead>
  <tbody id="sortableBody">
    @forelse($questions as $question)
    <tr data-id="{{ $question->id }}">
      <!-- <td scope="col" class="handle cursor-grab">{{ $question->display_order }}</td> -->
      <td scope="col" class="handle cursor-grab">{{ ($questions->currentPage() - 1) * $questions->perPage() + $loop->iteration }}</td>
      <td>{{ $question->question_text }}</td>
      <td>
        @switch($question->package)
        @case('awal_shift') Awal Shift @break
        @case('saat_bekerja') Saat Bekerja @break
        @case('setelah_istirahat') Setelah Istirahat @break
        @case('akhir_shift') Akhir Shift @break
        @case('leader') Leader @break
        @default Tidak Teridenfikasi @break
        @endswitch
      </td>
      <td class="text-center">{{ $question->created_at->format('d/m/Y') }}</td>
      <td class="d-flex gap-3 justify-content-center">
        <a href="{{ route('question.edit', $question->id) }}" class="btn">Edit</a>
        <form method="POST" action="{{ route('question.delete', $question->id) }}">
          @csrf
          @method('DELETE')
          <button type="submit" onclick="return confirm('Apakah yakin dihapus?')" class="btn">Delete</button>
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

<div class="mt-3">
  {{ $questions->links() }}
</div>