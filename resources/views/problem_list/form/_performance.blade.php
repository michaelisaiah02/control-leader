@php
    $role = explode('-', $type)[0];
@endphp
<div class="d-flex w-100 justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-center gap-2 w-100">
        <label for="date" class="col-md-6 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Tanggal</label>
        <input type="date" name="date" id="date" disabled class="form-control bg-primary-subtle" value="" />
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
        @if ($role === 'leader')
            <label for="leader" class="col-md-6 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">
                Nama Leader
            </label>
            <input type="text" name="leader" id="leader" disabled class="form-control bg-primary-subtle" value="" />
        @elseif ($role === 'supervisor')
            <label for="supervisor" class="col-md-6 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">
                Nama Supervisor
            </label>
            <input type="text" name="supervisor" id="supervisor" disabled class="form-control bg-primary-subtle" value="" />
        @else
            Tidak Valid.
        @endif
    </div>
    <div class="d-flex align-items-center gap-2 w-100">
        @if ($role === 'leader')
        <label for="operator" class="col-md-6 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">
            Nama Operator
        </label>
        <input type="text" name="operator" id="operator" disabled class="form-control bg-primary-subtle" value="" />
        @elseif ($role === 'supervisor')
            <label for="leader" class="col-md-6 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">
                Nama Leader
            </label>
            <input type="text" name="leader" id="leader" disabled class="form-control bg-primary-subtle" value="" />
        @else
            Tidak Valid.
        @endif
    </div>
</div>
<div class="d-flex justify-content-between w-100">
    <div class="d-flex align-items-center gap-2 w-100">
        <label for="problem" class="col-md-2 text-center form-label bg-primary text-white px-4 py-2 rounded shadow border border-white">Problem</label>
        <input type="text" name="problem" id="problem" disabled class="form-control bg-primary-subtle" value="">
    </div>
</div>