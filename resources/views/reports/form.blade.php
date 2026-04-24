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
    <div class="container-fluid dashboard-container max-w-800 mx-auto">
        <form method="GET" class="my-auto animate-fade-in mt-4">

            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-header bg-light border-bottom-0 py-3 rounded-top-4">
                    <h6 class="fw-bold text-secondary mb-0 text-uppercase small">
                        <i class="bi bi-sliders me-1"></i> Filter Parameter Report
                    </h6>
                </div>

                <div class="card-body p-4">

                    {{-- 🔥 PRE-SORT DEPARTEMEN DARI SERVER 🔥 --}}
                    @php
                        $userDeptId = auth()->user()->department_id;
                        $sortedDepartments = $departments->sortByDesc(function ($dept) use ($userDeptId) {
                            return $dept->id === $userDeptId ? 1 : 0;
                        });

                        // Array buat tracking siapa aja yg udah dirender biar gak ada yg ilang
                        $renderedSpvs = [];
                        $renderedLeaders = [];
                        $renderedOps = [];
                    @endphp

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
                                            <option value="{{ $department->id }}"
                                                {{ auth()->user()->department_id === $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter: Supervisor (LEVEL 1: Dept -> SPV) --}}
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

                                        @foreach ($sortedDepartments as $dept)
                                            @php $deptSpvs = $supervisors->where('department_id', $dept->id); @endphp
                                            @if ($deptSpvs->isNotEmpty())
                                                <optgroup label="🏢 {{ $dept->name }}" data-dept-id="{{ $dept->id }}">
                                                    @foreach ($deptSpvs as $supervisor)
                                                        @php $renderedSpvs[] = $supervisor->employeeID; @endphp
                                                        <option value="{{ $supervisor->employeeID }}"
                                                            {{ auth()->user()->employeeID === $supervisor->employeeID || auth()->user()->superior_id === $supervisor->employeeID ? 'selected' : '' }}>
                                                            {{ $supervisor->employeeID }} - {{ $supervisor->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach

                                        {{-- Fallback SPV --}}
                                        @php $orphanSpvs = $supervisors->whereNotIn('employeeID', $renderedSpvs); @endphp
                                        @if ($orphanSpvs->isNotEmpty())
                                            <optgroup label="Lainnya (Tanpa Dept)">
                                                @foreach ($orphanSpvs as $supervisor)
                                                    <option value="{{ $supervisor->employeeID }}"
                                                        {{ auth()->user()->employeeID === $supervisor->employeeID || auth()->user()->superior_id === $supervisor->employeeID ? 'selected' : '' }}>
                                                        {{ $supervisor->employeeID }} - {{ $supervisor->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>

                                {{-- Filter: Leader (LEVEL 2: Dept -> SPV -> Leader) --}}
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

                                            @foreach ($sortedDepartments as $dept)
                                                @php $deptSpvs = $supervisors->where('department_id', $dept->id); @endphp
                                                @foreach ($deptSpvs as $spv)
                                                    @php $spvLeaders = $leaders->where('superior_id', $spv->employeeID); @endphp
                                                    @if ($spvLeaders->isNotEmpty())
                                                        <optgroup label="🏢 {{ $dept->name }} | 👨‍💼 SPV: {{ $spv->name }}"
                                                            data-dept-id="{{ $dept->id }}">
                                                            @foreach ($spvLeaders as $leader)
                                                                @php $renderedLeaders[] = $leader->employeeID; @endphp
                                                                <option value="{{ $leader->employeeID }}"
                                                                    {{ auth()->user()->employeeID === $leader->employeeID ? 'selected' : '' }}>
                                                                    {{ $leader->employeeID }} - {{ $leader->name }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                @endforeach
                                            @endforeach

                                            {{-- Fallback Leader --}}
                                            @php $orphanLeaders = $leaders->whereNotIn('employeeID', $renderedLeaders); @endphp
                                            @if ($orphanLeaders->isNotEmpty())
                                                <optgroup label="Lainnya (Hierarki Terputus)">
                                                    @foreach ($orphanLeaders as $leader)
                                                        <option value="{{ $leader->employeeID }}"
                                                            {{ auth()->user()->employeeID === $leader->employeeID ? 'selected' : '' }}>
                                                            {{ $leader->employeeID }} - {{ $leader->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                    </div>
                                @endif

                                {{-- Filter: Operator (LEVEL 3: Dept -> SPV -> Leader -> Operator) --}}
                                @if ($type === 'operator')
                                    <div class="col-md-6">
                                        <label for="operator" class="form-label fw-bold text-secondary small text-uppercase mb-1">
                                            <i class="bi bi-person-gear me-1"></i> Operator
                                        </label>
                                        <select name="operator" id="operator" class="form-select bg-warning-subtle" required>
                                            <option value="" selected disabled>-- Pilih Operator --</option>

                                            @foreach ($sortedDepartments as $dept)
                                                @php $deptSpvs = $supervisors->where('department_id', $dept->id); @endphp
                                                @foreach ($deptSpvs as $spv)
                                                    @php $spvLeaders = $leaders->where('superior_id', $spv->employeeID); @endphp
                                                    @foreach ($spvLeaders as $ldr)
                                                        @php $ldrOps = $operators->where('superior_id', $ldr->employeeID); @endphp
                                                        @if ($ldrOps->isNotEmpty())
                                                            <optgroup
                                                                label="🏢 {{ $dept->name }} | 👨‍💼 {{ $spv->name }} | 🎖️ LDR: {{ $ldr->name }}"
                                                                data-dept-id="{{ $dept->id }}">
                                                                @foreach ($ldrOps as $operator)
                                                                    @php $renderedOps[] = $operator->employeeID; @endphp
                                                                    <option value="{{ $operator->employeeID }}">
                                                                        {{ $operator->employeeID }} - {{ $operator->name }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            @endforeach

                                            {{-- Fallback Operator --}}
                                            @php $orphanOps = $operators->whereNotIn('employeeID', $renderedOps); @endphp
                                            @if ($orphanOps->isNotEmpty())
                                                <optgroup label="Lainnya (Hierarki Terputus)">
                                                    @foreach ($orphanOps as $operator)
                                                        <option value="{{ $operator->employeeID }}">
                                                            {{ $operator->employeeID }} - {{ $operator->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                    </div>
                                @endif

                            </div>
                        @break

                    @endswitch

                    <hr class="opacity-10 mb-4">

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

            <div class="fixed-bottom bg-white border-top shadow-lg px-3 py-1 d-flex justify-content-between align-items-center"
                style="z-index: 1030;">
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                    <i class="bi bi-arrow-left me-2"></i> Back
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
            const monthInput = document.getElementById('month');
            if (!monthInput.value) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                monthInput.value = `${yyyy}-${mm}`;
            }

            const buttons = document.querySelectorAll('button[type="submit"]');
            buttons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const form = this.closest('form');
                    if (form.checkValidity()) {
                        const originalText = this.innerHTML;
                        this.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

                        buttons.forEach(b => {
                            if (b !== this) b.disabled = true;
                        });

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
            const allSupervisors = @json($supervisors);
            const allLeaders = @json($leaders);
            const allOperators = @json($operators);

            const deptSelect = document.getElementById('department');
            const spvSelect = document.getElementById('supervisor');
            const leaderSelect = document.getElementById('leader');
            const opSelect = document.getElementById('operator');

            let isSyncing = false;

            // 🔥 FUNGSI SAKTI: Pindahin Optgroup Departemen Terpilih ke Paling Atas 🔥
            const reorderOptgroups = (deptId) => {
                if (!deptId) return;
                [spvSelect, leaderSelect, opSelect].forEach(selectEl => {
                    if (!selectEl) return;
                    // Ambil SEMUA grup yang punya dept-id tersebut
                    const targetGroups = selectEl.querySelectorAll(
                        `optgroup[data-dept-id="${deptId}"]`);
                    const placeholder = selectEl.querySelector('option[disabled]');

                    if (targetGroups.length > 0 && placeholder) {
                        let insertAfterEl = placeholder;
                        targetGroups.forEach(group => {
                            insertAfterEl.after(group);
                            insertAfterEl = group; // Grup berikutnya ditempel setelah grup ini
                        });
                    }
                });
            };

            // Fungsi aman buat ganti value tanpa nabrak "pointer-events: none" punya Leader/SPV Auth
            const setSafeValue = (selectEl, value) => {
                if (selectEl && selectEl.style.pointerEvents !== 'none') {
                    selectEl.value = value;
                }
            };

            // FUNGSI REBUILD DROPDOWN UDAH DIHAPUS. KITA GAK MAU NGANCURIN HTML LAGI! 🛑

            // ==========================================
            // EVENT 1: DEPARTMENT BERUBAH
            // ==========================================
            if (deptSelect) {
                deptSelect.addEventListener('change', function() {
                    if (isSyncing) return;
                    isSyncing = true;

                    reorderOptgroups(this.value);

                    setSafeValue(spvSelect, '');
                    setSafeValue(leaderSelect, '');
                    setSafeValue(opSelect, '');

                    isSyncing = false;
                });
            }

            // ==========================================
            // EVENT 2: SUPERVISOR BERUBAH
            // ==========================================
            if (spvSelect) {
                spvSelect.addEventListener('change', function() {
                    if (isSyncing) return;
                    isSyncing = true;

                    const spv = allSupervisors.find(s => s.employeeID == this.value);
                    if (spv) {
                        if (spv.department_id) {
                            setSafeValue(deptSelect, spv.department_id);
                            reorderOptgroups(spv.department_id);
                        }
                    }

                    // Reset bawahan
                    setSafeValue(leaderSelect, '');
                    setSafeValue(opSelect, '');

                    isSyncing = false;
                });
            }

            // ==========================================
            // EVENT 3: LEADER BERUBAH
            // ==========================================
            if (leaderSelect) {
                leaderSelect.addEventListener('change', function() {
                    if (isSyncing) return;
                    isSyncing = true;

                    const leader = allLeaders.find(l => l.employeeID == this.value);
                    if (leader) {
                        const spv = allSupervisors.find(s => s.employeeID == leader.superior_id);
                        if (spv) {
                            setSafeValue(spvSelect, spv.employeeID);
                            if (spv.department_id) {
                                setSafeValue(deptSelect, spv.department_id);
                                reorderOptgroups(spv.department_id);
                            }
                        }
                    }

                    // Reset bawahan
                    setSafeValue(opSelect, '');

                    isSyncing = false;
                });
            }

            // ==========================================
            // EVENT 4: OPERATOR BERUBAH
            // ==========================================
            if (opSelect) {
                opSelect.addEventListener('change', function() {
                    if (isSyncing) return;
                    isSyncing = true;

                    const opId = this.value;
                    const op = allOperators.find(o => o.employeeID == opId);

                    if (op) {
                        const leader = allLeaders.find(l => l.employeeID == op.superior_id);
                        if (leader) {
                            setSafeValue(leaderSelect, leader.employeeID);

                            const spv = allSupervisors.find(s => s.employeeID == leader.superior_id);
                            if (spv) {
                                setSafeValue(spvSelect, spv.employeeID);
                                if (spv.department_id) {
                                    setSafeValue(deptSelect, spv.department_id);
                                    reorderOptgroups(spv.department_id);
                                }
                            }
                        }
                    }
                    isSyncing = false;
                });
            }

            // Inisialisasi awal
            setTimeout(() => {
                if (deptSelect && deptSelect.value) {
                    reorderOptgroups(deptSelect.value);
                }
            }, 100);
        });
    </script>
@endsection
