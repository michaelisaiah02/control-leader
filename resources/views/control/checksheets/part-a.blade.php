@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border-1 border-white rounded-2 text-uppercase">
        @switch($phase)
            @case('awal_shift')
                AWAL SHIFT SEBELUM BEKERJA
            @break

            @case('saat_bekerja')
                SAAT BEKERJA
            @break

            @case('setelah_istirahat')
                SETELAH ISTIRAHAT
            @break

            @case('akhir_shift')
                AKHIR SHIFT SEBELUM PULANG
            @break

            @default
                JUDUL CHECKSHEET
        @endswitch
    </p>
@endpush

@section('content')
    <div class="px-5">
        <div class="d-flex w-100 mt-2 justify-content-between align-items-center">
            <p class="border border-2 border-white bg-primary rounded-2 text-white py-2 px-4 shadow">Bagian A</p>
            <p class="border border-2 border-primary rounded-2 px-2 py-2 shadow">Stopwatch:
                <span id="stopwatch" class="py-1 px-2 text-danger bg-danger-subtle"
                    data-start="{{ $startedAtMs }}">00:00</span>
            </p>
        </div>

        <form id="formA" onsubmit="return false;">
            @csrf
            <input type="hidden" name="phase" value="{{ $phase }}">

            {{-- PAGE 1 --}}
            <div id="page1">
                <div class="mb-3 p-3 border border-2 rounded-4">
                    <div class="fw-semibold mb-2">1. Shift</div>
                    <div class="ms-3">
                        @foreach ([1, 2, 3] as $s)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shift" id="s{{ $s }}"
                                    value="{{ $s }}" {{ ($prefill['shift'] ?? null) == $s ? 'checked' : '' }}>
                                <label class="form-check-label" for="s{{ $s }}">{{ $s }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3 p-3 border border-2 rounded-4">
                    <div class="fw-semibold mb-2">2. {{ $targetLabel }}</div>
                    <select name="target_pick" class="form-select bg-warning-subtle" required>
                        <option value="">Nama Lengkap</option>
                        @foreach ($options as $opt)
                            <option value="{{ $opt['value'] }}"
                                {{ ($prefill['target_pick'] ?? '') === $opt['value'] ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 p-3 border border-2 rounded-4">
                    <div class="fw-semibold mb-2">3. Bagian</div>
                    <input type="text" name="bagian" class="form-control bg-warning-subtle"
                        placeholder="Contoh : Cutting" value="{{ $prefill['bagian'] ?? $deptName }}">
                </div>
            </div>

            {{-- PAGE 2 --}}
            <div id="page2" class="d-none">
                <div class="mb-3 p-3 border border-2 rounded-4">
                    <div class="fw-semibold mb-2">4. Check <u>kehadiran</u> operator</div>
                    <div class="ms-3 mb-3 small">
                        <div><u>Apabila operator tidak masuk</u> :</div>
                        <ul class="mb-2">
                            <li>Isi perubahan Man Power di Henkaten Board</li>
                            <li>Operator pengganti harus sesuai Skill Map</li>
                            <li>Konfirmasi hasil awal untuk operator pengganti (Hasil awal kerja OK)</li>
                        </ul>
                        <div>0) <span class="text-decoration-underline">Absen</span></div>
                        <div>1) <span class="text-decoration-underline">Hadir</span></div>
                    </div>

                    <div class="ms-2">
                        <label class="me-4">
                            <input type="radio" name="attendance" value="0"
                                {{ ($prefill['attendance'] ?? null) === '0' ? 'checked' : '' }}> Absen
                        </label>
                        <label>
                            <input type="radio" name="attendance" value="1"
                                {{ ($prefill['attendance'] ?? null) === '1' ? 'checked' : '' }}> Hadir
                        </label>
                    </div>
                </div>

                {{-- Kondisional Absen --}}
                <div id="absenWrap" class="{{ ($prefill['attendance'] ?? '') === '0' ? '' : 'd-none' }}">
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2">Nama Operator <u>Pengganti</u></div>
                        <input type="text" class="form-control bg-warning-subtle" name="nama_pengganti"
                            placeholder="Nama Lengkap" value="{{ $prefill['nama_pengganti'] ?? '' }}">
                    </div>
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2">Bagian Operator <u>Pengganti</u></div>
                        <input type="text" class="form-control bg-warning-subtle" name="bagian_pengganti"
                            placeholder="Contoh : Finishing" value="{{ $prefill['bagian_pengganti'] ?? '' }}">
                    </div>
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2"><u>Kondisi</u> Operator Pengganti</div>
                        <div class="ms-2">
                            <label class="me-4"><input type="radio" name="kondisi_pengganti" value="Sehat"
                                    {{ ($prefill['kondisi_pengganti'] ?? '') === 'Sehat' ? 'checked' : '' }}> Sehat</label>
                            <label><input type="radio" name="kondisi_pengganti" value="Sakit"
                                    {{ ($prefill['kondisi_pengganti'] ?? '') === 'Sakit' ? 'checked' : '' }}> Sakit</label>
                        </div>
                    </div>
                </div>

                {{-- Kondisional Hadir --}}
                <div id="hadirWrap" class="{{ ($prefill['attendance'] ?? '') === '1' ? '' : 'd-none' }}">
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2"><u>Kondisi</u> Operator</div>
                        <div class="ms-2">
                            <label class="me-4"><input type="radio" name="kondisi" value="Sehat"
                                    {{ ($prefill['kondisi'] ?? '') === 'Sehat' ? 'checked' : '' }}> Sehat</label>
                            <label><input type="radio" name="kondisi" value="Sakit"
                                    {{ ($prefill['kondisi'] ?? '') === 'Sakit' ? 'checked' : '' }}> Sakit</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-3">
                <button type="button" class="btn btn-primary d-none" id="prevBtn">Back</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(function() {
            // Stopwatch dari startedAtMs (persist)
            const startedAt = parseInt($('#stopwatch').data('start'), 10) || Date.now();
            setInterval(function() {
                const sec = Math.floor((Date.now() - startedAt) / 1000);
                const mm = String(Math.floor(sec / 60)).padStart(2, '0');
                const ss = String(sec % 60).padStart(2, '0');
                $('#stopwatch').text(`${mm}:${ss}`);
            }, 1000);

            // Heartbeat kecil
            setInterval(() => $.post("{{ route('control.heartbeat') }}", {
                _token: '{{ csrf_token() }}'
            }), 45000);

            let page = 1;
            $('#prevBtn').toggleClass('d-none', page === 1);

            $('[name="attendance"]').on('change', function() {
                const v = $(this).val();
                $('#absenWrap').toggleClass('d-none', v !== '0');
                $('#hadirWrap').toggleClass('d-none', v !== '1');
            });

            $('#prevBtn').on('click', function() {
                if (page === 2) {
                    $('#page2').addClass('d-none');
                    $('#page1').removeClass('d-none');
                    page = 1;
                    $('#prevBtn').toggleClass('d-none', page === 1);
                }
            });

            $('#nextBtn').on('click', function() {
                if (page === 1) {
                    const shift = $('[name="shift"]:checked').val();
                    const target_pick = $('[name="target_pick"]').val();
                    const bagian = $('[name="bagian"]').val()?.trim();
                    if (!shift || !target_pick || !bagian) return alert(
                        'Lengkapi Shift, Target, dan Bagian.');
                    $('#page1').addClass('d-none');
                    $('#page2').removeClass('d-none');
                    page = 2;
                    $('#prevBtn').toggleClass('d-none', page === 1);
                    return;
                }

                // Page 2 → commit + go to B
                const phase = $('[name="phase"]').val();
                const attendance = $('[name="attendance"]:checked').val();
                if (!attendance) return alert('Pilih Kehadiran.');

                const payload = {
                    _token: '{{ csrf_token() }}',
                    phase,
                    shift: $('[name="shift"]:checked').val(),
                    target_pick: $('[name="target_pick"]').val(),
                    bagian: $('[name="bagian"]').val(),
                    attendance,
                    nama_pengganti: $('[name="nama_pengganti"]').val(),
                    bagian_pengganti: $('[name="bagian_pengganti"]').val(),
                    kondisi_pengganti: $('[name="kondisi_pengganti"]:checked').val(),
                    kondisi: $('[name="kondisi"]:checked').val(),
                };

                $.post("{{ route('control.checksheets.commitTarget') }}", payload)
                    .done(() => window.location.href = "{{ route('control.checksheets.partB') }}?type=" +
                        encodeURIComponent(phase))
                    .fail(xhr => alert('Gagal menyimpan Bagian A: ' + xhr.status));
            });
        });
    </script>
@endsection
