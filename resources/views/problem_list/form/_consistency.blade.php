@php
    $pageRole = explode('-', $type)[0];
@endphp

<div class="row g-3">
    {{-- TANGGAL --}}
    <div class="col-md-3">
        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
            <i class="bi bi-calendar-event me-1"></i> Tanggal
        </label>
        <input type="text" class="form-control bg-light text-muted border-0 fw-bold"
            value="{{ \Carbon\Carbon::parse($problem->created_at)->format('d F Y') }}" readonly>
    </div>

    {{-- NAMA TARGET (Yang kena problem konsistensi) --}}
    <div class="col-md-5">
        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
            <i class="bi bi-person-badge me-1"></i> Nama ({{ ucfirst($pageRole) }})
        </label>
        <input type="text" class="form-control bg-light text-muted border-0"
            value="{{ $problem->user->employeeID . ' - ' . $problem->user->name ?? '-' }}" readonly>
    </div>

    {{-- REMARK (Miss / Late / Advanced) --}}
    <div class="col-md-4">
        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
            <i class="bi bi-flag-fill me-1"></i> Remark
        </label>
        <input type="text" class="form-control bg-light text-danger fw-bold border-0" value="{{ $problem->remark }}"
            readonly>
    </div>

    {{-- PROBLEM --}}
    <div class="col-12 mt-3">
        <label class="form-label small fw-bold text-danger text-uppercase mb-1">
            <i class="bi bi-exclamation-circle me-1"></i> Problem Detail
        </label>
        <textarea class="form-control bg-light text-dark border-0 shadow-sm" rows="2" readonly>{{ $problem->problem }}</textarea>

        {{-- Bantuan penjelasan otomatis di bawah textbox --}}
        <div class="form-text mt-1 text-muted small">
            @if ($problem->remark === 'Miss')
                <i class="bi bi-info-circle"></i> Indikasi: Tidak mengisi checksheet sesuai jadwal yang ditentukan.
            @elseif($problem->remark === 'Late')
                <i class="bi bi-info-circle"></i> Indikasi: Mengisi checksheet melebihi batas standar waktu pengisian.
            @elseif($problem->remark === 'Advanced')
                <i class="bi bi-info-circle"></i> Indikasi: Mengisi checksheet terlalu cepat dari jadwal seharusnya.
            @endif
        </div>
    </div>
</div>
