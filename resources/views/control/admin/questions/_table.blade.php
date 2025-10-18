<table class="my-2 table table-striped table-bordered" id="sortableTable">
  <thead class="table-primary">
    <tr>
      <th width="5%">No</th>
      <th>Pertanyaan</th>
      <th>Type</th>
      <th>Date Created</th>
      <th width="15%">Action</th>
    </tr>
  </thead>
  <tbody id="sortableBody">
    @forelse($questions as $question)
    <tr data-id="{{ $question }}">
      <td class="handle cursor-grab">{{ $question->display_order }}</td>
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
      <td>{{ $question->created_at }}</td>
      <td>
        <a href="{{ route('question.edit', $question->id) }}">Edit</a>
        <form method="POST" action="{{ route('question.delete', $question->id) }}">
          @csrf
          @method('DELETE')
          <button type="submit" onclick="return confirm('Apakah yakin dihapus?')">Delete</button>
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