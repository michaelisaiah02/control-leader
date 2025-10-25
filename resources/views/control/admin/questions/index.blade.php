@extends('layouts.app')

@push('subtitle')
<p class="fs-2 w-75 p-0 my-auto sub-judul border border-white rounded-2 text-uppercase">
  List Questions
</p>
@endpush

@section('styles')
<style>
  .cursor-grab {
    cursor: grab;
  }

  .sortable-ghost {
    opacity: 0.4;
    background: #f0f0f0;
  }
</style>
@endsection

@section('content')
<div class="px-5">
  <form id="filterForm" class="w-100 d-flex gap-2">
    <label for="filterPackage">Filter</label>
    <select id="filterPackage" class="form-select w-auto">
      <option value="">-- Semua</option>
      <option value="awal_shift">Awal Shift</option>
      <option value="saat_bekerja">Saat Bekerja</option>
      <option value="setelah_istirahat">Setelah Istirahat</option>
      <option value="akhir_shift">Akhir Shift</option>
      <option value="leader">Leader</option>
    </select>
  </form>

  <div id="questionTable">
    @include('control.admin.questions._table', ['questions' => $questions])
  </div>

  <div class="py-1 d-flex justify-content-between">
    <a href="{{ route('question.create') }}" class="btn btn-primary text-white py-2 px-4 rounded-full">+</a>
    <a href="#" class="btn btn-primary text-white py-2 px-4">Back</a>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/Sortable.min.js') }}"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const tableContainer = document.querySelector('#sortableTable');
    const filterForm = document.querySelector('#filterForm');
    const filterPackage = document.querySelector('#filterPackage');

    function loadQuestions(url) {
      fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(res => res.json())
        .then(data => {
          tableContainer.innerHTML = data.html;
          attachPaginationEvents();
          initSortable(); // re-init setelah update table
        })
        .catch(err => console.error(err));
    }

    // Filter: Type Pertanyaan
    filterPackage.addEventListener('change', () => {
      const pkg = filterPackage.value;
      let url = `{{ route('question.index') }}`;
      if (pkg) url += `?package=${encodeURIComponent(pkg)}`;
      loadQuestions(url);
    });

    function attachPaginationEvents() {
      document.querySelectorAll('#questionTable .pagination a').forEach(link => {
        link.addEventListener('click', e => {
          e.preventDefault();
          loadQuestions(e.target.getAttribute('href'));
        });
      });
    }

    function initSortable() {
      const tbody = document.querySelector('#sortableBody');
      if (!tbody) return;

      Sortable.create(tbody, {
        handle: '.handle',
        animation: 150,
        onEnd: function() {
          const order = [];
          document.querySelectorAll('#sortableBody tr').forEach((row, index) => {
            order.push({
              id: row.dataset.id,
              display_order: index + 1
            });
            row.querySelector('td:first-child').textContent = index + 1;
          });

          fetch(`{{ route('question.updateOrder') }}`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
              },
              body: JSON.stringify({
                order
              }),
            }).then(res => res.json())
            .then(() => console.log('Urutan berhasil disimpan'))
            .catch(err => console.error(err));
        }
      });
    }
    initSortable();
  });
</script>
@endsection