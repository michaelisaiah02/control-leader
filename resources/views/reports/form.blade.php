@extends('layouts.app')

@push('subtitle')
    <div
        class="d-inline-flex align-items-center justify-content-center px-4 py-1 mt-1 mb-0 rounded-pill bg-white bg-opacity-10 text-white animate-fade-in subtitle">
        <i class="bi bi-funnel me-2 fs-5"></i>
        <span class="fs-5 fw-bold text-uppercase">
            @switch($type)
                @case('leader')
                    Leader Performance Report
                @break

                @case('supervisor')
                    Supervisor Performance Report
                @break

                @case('operator')
                    Operator Performance Report
                @break

                @default
                    Performance Report
            @endswitch
        </span>
    </div>
@endpush

@section('content')
    <div class="container-fluid max-w-800 px-0 mx-0 animate-fade-in">

        {{-- Form method GET biar data filter masuk ke URL (bisa di-bookmark/share) --}}
        <form method="GET">

            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-header bg-light border-bottom-0 py-2 rounded-top-4">
                    <h6 class="fw-bold text-secondary mb-0 text-uppercase small">
                        <i class="bi bi-sliders me-1"></i> Filter Parameter Report
                    </h6>
                </div>

                <div class="card-body p-3">

                    {{-- DYNAMIC FILTER INPUTS --}}
                    @switch($type)
                        @case('leader')
                        @case('supervisor')

                        @case('operator')
                            <div class="row g-4 mb-4">
                                {{-- Filter: Department --}}
                                <div class="col-md-6">
                                    <label for="department" class="form-label fw-bold text-secondary small text-uppercase mb-1">
                                        <i class="bi bi-building me-1"></i> Department
                                    </label>
                                    <select name="department" id="department"
                                        class="form-select {{ auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor' ? 'bg-light' : 'bg-warning-subtle' }}"
                                        required
                                        style="{{ auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor' ? 'pointer-events: none;' : '' }}">
                                        <option value=""
                                            {{ auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor' ? '' : 'selected' }}
                                            disabled>-- Pilih Department --</option>
                                        @foreach ($departments as $department)
                                            {{-- @dd(auth()->user()->department_id === $department->id) --}}
                                            <option value="{{ $department->id }}"
                                                {{ auth()->user()->department_id === $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter: Supervisor --}}
                                <div class="col-md-6">
                                    <label for="supervisor" class="form-label fw-bold text-secondary small text-uppercase mb-1">
                                        <i class="bi bi-person-workspace me-1"></i> Supervisor
                                    </label>
                                    <select name="supervisor" id="supervisor"
                                        class="form-select {{ auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor' ? 'bg-light' : 'bg-warning-subtle' }}"
                                        required
                                        style="{{ auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor' ? 'pointer-events: none;' : '' }}">
                                        <option value=""
                                            {{ auth()->user()->role === 'leader' || auth()->user()->role === 'supervisor' ? '' : 'selected' }}
                                            disabled>-- Pilih Supervisor --</option>
                                        @foreach ($supervisors as $supervisor)
                                            <option value="{{ $supervisor->employeeID }}"
                                                {{ (auth()->user()->employeeID === $supervisor->employeeID ? 'selected' : auth()->user()->superior_id === $supervisor->employeeID) ? 'selected' : '' }}>
                                                {{ $supervisor->employeeID }} - {{ $supervisor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter: Leader (Muncul untuk report Leader & Operator) --}}
                                @if ($type === 'leader' || $type === 'operator')
                                    <div class="col-md-6">
                                        <label for="leader" class="form-label fw-bold text-secondary small text-uppercase mb-1">
                                            <i class="bi bi-person-badge me-1"></i> Leader
                                        </label>
                                        <select name="leader" id="leader"
                                            class="form-select {{ auth()->user()->role === 'leader' ? ' bg-light' : 'bg-warning-subtle' }}"
                                            required
                                            style="{{ auth()->user()->role === 'leader' ? 'pointer-events: none;' : '' }}">
                                            <option value="" {{ auth()->user()->role !== 'leader' ? 'selected' : '' }}
                                                disabled>-- Pilih Leader --</option>
                                            @foreach ($leaders as $leader)
                                                <option value="{{ $leader->employeeID }}"
                                                    {{ auth()->user()->employeeID === $leader->employeeID ? 'selected' : '' }}>
                                                    {{ $leader->employeeID }} - {{ $leader->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- Filter: Operator (Muncul HANYA untuk report Operator) --}}
                                @if ($type === 'operator')
                                    <div class="col-md-6">
                                        <label for="operator" class="form-label fw-bold text-secondary small text-uppercase mb-1">
                                            <i class="bi bi-person-gear me-1"></i> Operator
                                        </label>
                                        <select name="operator" id="operator" class="form-select bg-warning-subtle" required>
                                            <option value="" selected disabled>-- Pilih Operator --</option>
                                            @foreach ($operators as $operator)
                                                <option value="{{ $operator->employeeID }}">
                                                    {{ $operator->employeeID }} - {{ $operator->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                            </div>
                        @break
                    @endswitch

                    <hr class="opacity-10 mb-4">

                    {{-- PERIODE BULAN --}}
                    <div class="row">
                        <div class="col-md-6">
                            <label for="month" class="form-label fw-bold text-primary small text-uppercase mb-1">
                                <i class="bi bi-calendar-event me-1"></i> Periode (Bulan & Tahun)
                            </label>
                            <input type="month" name="month" id="month"
                                class="form-control border-primary shadow-sm" max="{{ date('Y-m') }}" required />
                        </div>
                    </div>

                </div>
            </div>

            {{-- STICKY ACTION BAR --}}
            <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center"
                style="z-index: 1030;">
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>

                <div class="d-flex gap-2">
                    @switch($type)
                        @case('leader')
                        @case('supervisor')
                            <button type="submit" formaction="{{ route('reports.consistency', ['type' => $type]) }}"
                                class="btn btn-info rounded-pill px-4 fw-bold shadow-sm text-white">
                                <i class="bi bi-graph-up me-1"></i> Consistency Report
                            </button>
                            <button type="submit" formaction="{{ route('reports.score', ['type' => $type]) }}"
                                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-star me-1"></i> Score Report
                            </button>
                        @break

                        @case('operator')
                            <button type="submit" formaction="{{ route('reports.score', ['type' => $type]) }}"
                                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-star me-1"></i> View Score Report
                            </button>
                        @break
                    @endswitch
                </div>
            </div>

        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Set default value bulan ini
            const monthInput = document.getElementById('month');
            if (!monthInput.value) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                monthInput.value = `${yyyy}-${mm}`;
            }

            // UX Enhancement: Tambahin loading text saat tombol ditekan
            const buttons = document.querySelectorAll('button[type="submit"]');
            buttons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const form = this.closest('form');
                    if (form.checkValidity()) {
                        const originalText = this.innerHTML;
                        this.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                        // Kita gabisa nge-disable button yg diklik, karena form bakal ilang data formaction-nya,
                        // jadi disable button lainnya aja
                        buttons.forEach(b => {
                            if (b !== this) b.disabled = true;
                        });

                        // Kembalikan tulisan seperti semula setelah bbrp detik (karena ini cuma get request yg bakal buka page baru)
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            buttons.forEach(b => b.disabled = false);
                        }, 3000);
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Ubah SEMUA data dari Laravel ke format Javascript Array
            // Biar kita bisa lacak silsilah keluarganya sampe ke akar 🌳
            const allDepartments = @json($departments);
            const allSupervisors = @json($supervisors);
            const allLeaders = @json($leaders);
            const allOperators = @json($operators);

            // 2. Tangkap elemen DOM
            const deptSelect = document.getElementById('department');
            const spvSelect = document.getElementById('supervisor');
            const leaderSelect = document.getElementById('leader');
            const opSelect = document.getElementById('operator');

            // Flag anti-kiamat (mencegah infinite loop pas auto-fill saling trigger)
            let isSyncing = false;

            // Fungsi sakti buat nge-rebuild dropdown tanpa ngerusak UX
            const rebuildDropdown = (selectEl, data, selectedValue, defaultText) => {
                if (!selectEl) return;
                selectEl.innerHTML =
                    `<option value="" disabled ${!selectedValue ? 'selected' : ''}>${defaultText}</option>`;
                data.forEach(item => {
                    const isSelected = item.employeeID == selectedValue ? 'selected' : '';
                    selectEl.innerHTML +=
                        `<option value="${item.employeeID}" ${isSelected}>${item.employeeID} - ${item.name}</option>`;
                });
            };

            // ==========================================
            // SCENARIO 1: BOTTOM-UP (Pilih Operator -> Auto-fill Leader, SPV, Dept)
            // ==========================================
            if (opSelect) {
                opSelect.addEventListener('change', function() {
                    if (isSyncing) return;
                    isSyncing = true; // Nyalain shield 🛡️

                    const opId = this.value;
                    const op = allOperators.find(o => o.employeeID == opId);

                    if (op) {
                        const leader = allLeaders.find(l => l.employeeID == op.superior_id);
                        if (leader) {
                            const spv = allSupervisors.find(s => s.employeeID == leader.superior_id);

                            // 1. Set Supervisor & Department
                            if (spv) {
                                if (deptSelect && spv.department_id) deptSelect.value = spv.department_id;
                                if (spvSelect) spvSelect.value = spv.employeeID;

                                // 2. Rebuild Leader dropdown khusus untuk SPV ini
                                rebuildDropdown(leaderSelect, allLeaders.filter(l => l.superior_id == spv
                                    .employeeID), leader.employeeID, '-- Pilih Leader --');
                            }

                            // 3. Rebuild Operator dropdown khusus untuk Leader ini
                            rebuildDropdown(opSelect, allOperators.filter(o => o.superior_id == leader
                                .employeeID), opId, '-- Pilih Operator --');
                        }
                    }

                    isSyncing = false; // Matiin shield
                });
            }

            // ==========================================
            // SCENARIO 2: MID-WAY (Pilih Leader -> Auto-fill SPV, Dept, Reset Operator)
            // ==========================================
            if (leaderSelect) {
                leaderSelect.addEventListener('change', function() {
                    if (isSyncing) return;
                    isSyncing = true;

                    const leaderId = this.value;
                    const leader = allLeaders.find(l => l.employeeID == leaderId);

                    if (leader) {
                        const spv = allSupervisors.find(s => s.employeeID == leader.superior_id);

                        if (spv) {
                            if (deptSelect && spv.department_id) deptSelect.value = spv.department_id;
                            if (spvSelect) spvSelect.value = spv.employeeID;

                            rebuildDropdown(leaderSelect, allLeaders.filter(l => l.superior_id == spv
                                .employeeID), leaderId, '-- Pilih Leader --');
                        }

                        // Reset anak (Operator)
                        rebuildDropdown(opSelect, allOperators.filter(o => o.superior_id == leaderId), null,
                            '-- Pilih Operator --');
                    }

                    isSyncing = false;
                });
            }

            // ==========================================
            // SCENARIO 3: TOP-DOWN (Pilih SPV -> Auto-fill Dept, Reset Leader & Operator)
            // ==========================================
            if (spvSelect) {
                spvSelect.addEventListener('change', function() {
                    if (isSyncing) return;
                    isSyncing = true;

                    const spvId = this.value;
                    const spv = allSupervisors.find(s => s.employeeID == spvId);

                    if (spv) {
                        // Set parent (Dept)
                        if (deptSelect && spv.department_id) deptSelect.value = spv.department_id;

                        // Reset anak-anaknya
                        rebuildDropdown(leaderSelect, allLeaders.filter(l => l.superior_id == spvId), null,
                            '-- Pilih Leader --');
                        rebuildDropdown(opSelect, [], null, '-- Pilih Operator --');
                    }

                    isSyncing = false;
                });
            }

            // Trigger inisial pas page load (buat nangkep auth()->user() yang otomatis selected dari server)
            setTimeout(() => {
                if (opSelect && opSelect.value) {
                    opSelect.dispatchEvent(new Event('change'));
                } else if (leaderSelect && leaderSelect.value) {
                    leaderSelect.dispatchEvent(new Event('change'));
                } else if (spvSelect && spvSelect.value) {
                    spvSelect.dispatchEvent(new Event('change'));
                }
            }, 100); // Kasih delay dikit biar DOM render sempurna dulu
        });
    </script>
@endsection
