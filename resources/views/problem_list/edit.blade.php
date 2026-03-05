@extends('layouts.app')

@php
    $map = [
        'leader-performance' => 'Edit Leader Performance Problem',
        'leader-consistency' => 'Edit Leader Consistency Problem',
        'supervisor-performance' => 'Edit Supervisor Performance Problem',
        'supervisor-consistency' => 'Edit Supervisor Consistency Problem',
    ];

    // 1. Dapatkan role dari halaman yang sedang dibuka (leader / supervisor)
    $pageRole = explode('-', $type)[0];

    // 2. Dapatkan role user yang sedang login
    $loggedInRole = auth()->user()->role ?? 'guest';

    // 3. Hak Akses Edit Countermeasure (Hanya pemilik role halaman ini)
    $canEditCountermeasure = $loggedInRole === $pageRole;

    // 4. Hak Akses Edit Status & Due Date (Hanya Atasan 1 tingkat di atasnya)
    $canEditStatus = false;
    $superiorRoleName = 'Atasan';

    if ($pageRole === 'leader') {
        // Atasan Leader adalah Supervisor
        $canEditStatus = in_array($loggedInRole, ['supervisor']);
        $superiorRoleName = 'Supervisor';
    } elseif ($pageRole === 'supervisor') {
        // Atasan Supervisor adalah Management / Admin / YPQ
        $canEditStatus = in_array($loggedInRole, ['management', 'admin', 'ypq']);
        $superiorRoleName = 'Management';
    }

    // 5. Validasi Due Date cuma 1 kali edit
    $canEditDueDate = $canEditStatus && !$problem->is_due_date_changed;
@endphp

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 border border-light text-white animate-fade-in subtitle">
        <i class="bi bi-pencil-square me-2 fs-5"></i>
        <span class="fs-5 fw-bold text-uppercase">
            {{ $map[$type] ?? 'Tipe Tidak Valid' }}
        </span>
    </div>
@endpush

@section('content')
    <div class="container-fluid max-w-800 mx-auto px-0">

        <form method="POST" action="{{ route('listProblem.update', ['type' => $type, 'id' => $problem->id]) }}">
            @csrf
            @method('PUT')

            <div class="card border-0 shadow-sm rounded-4 animate-fade-in">

                {{-- SECTION 1: REFERENCE DATA (READ-ONLY) --}}
                <div class="card-header bg-light border-bottom-0 py-1 rounded-top-4">
                    <h6 class="fw-bold text-secondary mb-2 text-uppercase small">
                        <i class="bi bi-info-circle-fill me-1"></i> Detail Problem
                    </h6>

                    {{-- Area abu-abu biar user tau ini data referensi --}}
                    <div class="bg-white p-3 rounded-3 border">
                        @switch($type)
                            @case('leader-performance')
                            @case('supervisor-performance')
                                @include('problem_list.form._performance')
                            @break

                            @case('leader-consistency')
                            @case('supervisor-consistency')
                                @include('problem_list.form._consistency')
                            @break
                        @endswitch
                    </div>
                </div>

                <div class="card-body p-3">
                    {{-- SECTION 2: LEADER ACTION (COUNTERMEASURE) --}}
                    <div class="mb-3">
                        <label for="countermeasure"
                            class="form-label fw-bold {{ $canEditCountermeasure ? 'text-primary' : 'text-secondary' }} small text-uppercase">
                            <i class="bi bi-wrench me-1"></i> Countermeasure
                        </label>

                        {{-- Kalau bukan leader, background jadi abu-abu --}}
                        <textarea name="countermeasure" id="countermeasure" rows="2"
                            class="form-control {{ !$canEditCountermeasure ? 'bg-light text-muted' : 'border-primary' }}"
                            placeholder="Ketik tindakan perbaikan di sini..." {{ !$canEditCountermeasure ? 'readonly' : '' }}>{{ $problem->countermeasure }}</textarea>

                        @if (!$canEditCountermeasure)
                            <div class="form-text small mt-1"><i class="bi bi-lock"></i>
                                Hanya <strong>{{ ucfirst($pageRole) }}</strong> yang dapat mengubah ini.
                            </div>
                        @endif
                    </div>

                    <hr class="opacity-10 mb-3 mt-1">

                    {{-- SECTION 3: SUPERVISOR ACTION (STATUS & DUE DATE) --}}
                    <div class="row g-3">
                        <div class="col-md-6 mt-2">
                            <label for="status"
                                class="form-label fw-bold {{ $canEditStatus ? 'text-primary' : 'text-secondary' }} small text-uppercase">
                                <i class="bi bi-tag me-1"></i> Status
                            </label>
                            {{-- FIX: name="department" diubah jadi name="status" --}}
                            <select name="status" id="status"
                                class="form-select {{ !$canEditStatus ? 'bg-light text-muted' : 'border-primary' }}"
                                required {{ !$canEditStatus ? 'disabled' : '' }}>

                                <option value="" disabled>-- Pilih Status --</option>
                                <option value="open" {{ $problem->status == 'open' ? 'selected' : '' }}>🔴 Open</option>
                                <option value="follow_up_1" {{ $problem->status == 'follow_up_1' ? 'selected' : '' }}>🟡
                                    Follow Up 1</option>
                                <option value="close" {{ $problem->status == 'close' ? 'selected' : '' }}>🟢 Close
                                </option>
                            </select>
                            {{-- Hidden input buat nge-pass data kalau select-nya didisable --}}
                            @if (!$canEditStatus)
                                <input type="hidden" name="status" value="{{ $problem->status }}">
                            @endif
                        </div>

                        <div class="col-md-6 mt-2">
                            <label for="due_date"
                                class="form-label fw-bold {{ $canEditStatus ? 'text-primary' : 'text-secondary' }} small text-uppercase">
                                <i class="bi bi-calendar-event me-1"></i> Due Date
                            </label>

                            @php
                                // Supervisor bisa edit KALAU due date belum pernah diubah
                                $canEditDueDate = $canEditStatus && !$problem->is_due_date_changed;
                            @endphp

                            <input type="date" name="due_date" id="due_date"
                                class="form-control {{ !$canEditDueDate ? 'bg-light text-muted' : 'border-primary' }}"
                                value="{{ \Carbon\Carbon::parse($problem->due_date)->format('Y-m-d') }}" required
                                {{ !$canEditDueDate ? 'readonly' : '' }}>

                            {{-- Notifikasi tambahan biar user nggak bingung kenapa kekunci --}}
                            @if ($canEditStatus && $problem->is_due_date_changed)
                                <div class="form-text small text-warning mt-1">
                                    <i class="bi bi-info-circle"></i> Due Date sudah pernah direvisi dan tidak dapat diubah
                                    lagi.
                                </div>
                            @endif
                        </div>

                        @if (!$canEditStatus)
                            <div class="col-12 mt-1">
                                <div class="form-text small"><i class="bi bi-lock"></i>
                                    Hanya <strong>{{ $superiorRoleName }}</strong> yang dapat mengubah
                                    Status & Due Date.
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- STICKY ACTION BAR --}}
            <div class="action-bar d-flex justify-content-between align-items-center px-3 px-md-5 py-1 mt-3 bg-white border-top shadow-lg"
                style="z-index: 1030;">
                <a href="{{ route('listProblem.list', $type) }}"
                    class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>

                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm" id="btn-save">
                    <i class="bi bi-save me-2"></i> Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Efek Loading saat submit form
            const form = document.querySelector('form');
            const btnSave = document.getElementById('btn-save');

            form.addEventListener('submit', function() {
                if (form.checkValidity()) {
                    btnSave.disabled = true;
                    btnSave.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
                }
            });
        });
    </script>
@endsection
