@php
    // Identifikasi siapa yang lagi diaudit berdasarkan tipe URL
    $pageRole = explode('-', $type)[0];
@endphp

<div class="row g-3">
    {{-- Tanggal --}}
    <div class="col-md-2 mt-1">
        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
            <i class="bi bi-calendar-event me-1"></i> Tanggal
        </label>
        <input type="text" class="form-control bg-light text-muted border-0 fw-bold"
            value="{{ \Carbon\Carbon::parse($problem->created_at)->format('d F Y') }}" readonly>
    </div>

    {{-- Person 1 (Auditor / Yang Ngecek) --}}
    <div class="col-md-5 mt-1">
        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
            <i class="bi bi-person-badge me-1"></i> Nama ({{ ucfirst($pageRole) }})
        </label>
        {{-- Panggil dari relasi $problem->user --}}
        <input type="text" class="form-control bg-light text-muted border-0"
            value="{{ $problem->user->employeeID . ' - ' . $problem->user->name ?? '-' }}" readonly>
    </div>

    {{-- Person 2 (Auditee / Target yang dicek) --}}
    <div class="col-md-5 mt-1">
        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
            <i class="bi bi-person-gear me-1"></i> Nama ({{ $pageRole === 'leader' ? 'Operator' : 'Leader' }})
        </label>
        <input type="text" class="form-control bg-light text-muted border-0"
            value="{{ $problem->inferior->employeeID . ' - ' . $problem->inferior->name ?? '-' }}" readonly>
    </div>

    {{-- Problem Description --}}
    <div class="col-12 mt-1">
        <label class="form-label small fw-bold text-danger text-uppercase mb-1">
            <i class="bi bi-exclamation-octagon me-1"></i> Problem
        </label>
        <textarea class="form-control bg-light text-dark border-0 shadow-sm" rows="2" readonly>{{ $problem->problem }}</textarea>
    </div>
</div>
