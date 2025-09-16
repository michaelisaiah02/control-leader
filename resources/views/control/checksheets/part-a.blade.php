@extends('layouts.app')

@push('subtitle')
    <p class="fs-2 w-75 p-0 my-auto sub-judul border-1 border-white rounded-2 text-uppercase">
        @switch($type)
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
                <span id="stopwatch" class="py-1 px-2 text-danger bg-danger-subtle">00:00</span>
            </p>
        </div>

        <form id="partA" onsubmit="return false;">
            @csrf
            <input type="hidden" name="schedule_detail_id" value="{{ $detail->id }}">

            {{-- ====== PAGE 1 ====== --}}
            <div id="page1">
                <div class="mb-3 p-3 border border-2 rounded-4">
                    <div class="fw-semibold mb-2">1. Shift</div>
                    <div class="ms-3">
                        <div class="form-check"><input class="form-check-input" type="radio" name="shift" value="1"
                                id="shift1"> <label class="form-check-label" for="shift1">1</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="shift" value="2"
                                id="shift2"> <label class="form-check-label" for="shift2">2</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="shift" value="3"
                                id="shift3"> <label class="form-check-label" for="shift3">3</label></div>
                    </div>
                </div>

                <div class="mb-3 p-3 border border-2 rounded-4">
                    <div class="fw-semibold mb-2">2. {{ $targetLabel }}</div>
                    <select name="target_pick" class="form-select bg-warning-subtle" required>
                        <option value="">Nama Lengkap</option>
                        @foreach ($options as $opt)
                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 p-3 border border-2 rounded-4">
                    <div class="fw-semibold mb-2">3. Bagian</div>
                    <input type="text" name="bagian" class="form-control bg-warning-subtle"
                        placeholder="Contoh : Cutting" value="{{ $deptName }}">
                </div>
            </div>

            {{-- ====== PAGE 2 ====== --}}
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
                        <div>0) <a class="text-decoration-underline" href="javascript:void(0)">Absen</a></div>
                        <div>1) <a class="text-decoration-underline" href="javascript:void(0)">Hadir</a></div>
                    </div>

                    <div class="ms-2">
                        <label class="me-4"><input type="radio" name="attendance" value="0"> Absen</label>
                        <label><input type="radio" name="attendance" value="1"> Hadir</label>
                    </div>
                </div>

                {{-- Kondisional muncul sesuai pilihan --}}
                <div id="absenWrap" class="d-none">
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2">Nama Operator <u>Pengganti</u></div>
                        <input type="text" class="form-control bg-warning-subtle" name="nama_pengganti"
                            placeholder="Nama Lengkap">
                    </div>
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2">Bagian Operator <u>Pengganti</u></div>
                        <input type="text" class="form-control bg-warning-subtle" name="bagian_pengganti"
                            placeholder="Contoh : Finishing">
                    </div>
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2"><u>Kondisi</u> Operator Pengganti</div>
                        <div class="ms-2">
                            <label class="me-4"><input type="radio" name="kondisi_pengganti" value="Sehat">
                                Sehat</label>
                            <label><input type="radio" name="kondisi_pengganti" value="Sakit"> Sakit</label>
                        </div>
                    </div>
                </div>

                <div id="hadirWrap" class="d-none">
                    <div class="mb-3 p-3 border border-2 rounded-4">
                        <div class="fw-semibold mb-2"><u>Kondisi</u> Operator</div>
                        <div class="ms-2">
                            <label class="me-4"><input type="radio" name="kondisi" value="Sehat"> Sehat</label>
                            <label><input type="radio" name="kondisi" value="Sakit"> Sakit</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-3">
                <button type="button" class="btn btn-primary" id="prevBtn">Back</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(function() {
            const detailId = {{ $detail->id }};
            const PARTB_URL = "{{ route('control.checksheets.partB', $detail) }}";
            const COMMIT_URL = "{{ route('control.checksheets.commitTarget', $detail) }}";
            const key = k => `cl:${detailId}:${k}`;

            // Stopwatch persist
            if (!sessionStorage.getItem(key('startAt'))) sessionStorage.setItem(key('startAt'), Date.now());
            setInterval(function() {
                const s = Math.floor((Date.now() - Number(sessionStorage.getItem(key('startAt')))) / 1000);
                const m = String(Math.floor(s / 60)).padStart(2, '0');
                const sec = String(s % 60).padStart(2, '0');
                $('#stopwatch').text(`${m}:${sec}`);
            }, 1000);

            // Heartbeat
            setInterval(function() {
                $.post("{{ route('control.heartbeat') }}", {
                    _token: '{{ csrf_token() }}'
                });
            }, 45000);

            // Pager
            let page = 1;
            $('#prevBtn').hide();

            $('#nextBtn').on('click', function() {
                if (page === 1) {
                    // Validasi halaman 1
                    const shift = $('input[name="shift"]:checked').val();
                    const pick = $('[name="target_pick"]').val();
                    const bagian = $('[name="bagian"]').val();
                    if (!shift || !pick || !bagian) {
                        alert('Lengkapi Shift, Target, dan Bagian.');
                        return;
                    }

                    // simpan sementara (buat part B)
                    sessionStorage.setItem(key('partA_tmp'), JSON.stringify({
                        shift,
                        target_pick: pick,
                        bagian
                    }));

                    // ke page 2
                    $('#page1').addClass('d-none');
                    $('#page2').removeClass('d-none');
                    $('#prevBtn').show();
                    page = 2;
                    return;
                }

                // page 2 → validasi kehadiran + commit target → redirect Part B
                const att = $('input[name="attendance"]:checked').val();
                if (!att) {
                    alert('Pilih Kehadiran.');
                    return;
                }

                // commit target (idempotent) agar detail punya target yg dipilih
                const pick = JSON.parse(sessionStorage.getItem(key('partA_tmp')) || '{}').target_pick;
                $.ajax({
                    url: COMMIT_URL,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        target_pick: pick
                    },
                    success: function() {
                        // simpan Part A final ke sessionStorage
                        const tmp = JSON.parse(sessionStorage.getItem(key('partA_tmp')) ||
                            '{}');
                        const payload = {
                            shift: tmp.shift,
                            target_pick: tmp.target_pick,
                            bagian: tmp.bagian,
                            attendance: att,
                            // kondisional
                            nama_pengganti: $('input[name="nama_pengganti"]').val() || null,
                            bagian_pengganti: $('input[name="bagian_pengganti"]').val() ||
                                null,
                            kondisi_pengganti: $('input[name="kondisi_pengganti"]:checked')
                                .val() || null,
                            kondisi: $('input[name="kondisi"]:checked').val() || null,
                        };
                        sessionStorage.setItem(key('partA'), JSON.stringify(payload));

                        const q = new URLSearchParams({
                            attendance: att
                        });
                        window.location.href = PARTB_URL + '?' + q.toString();
                    },
                    error: function(xhr) {
                        alert('Gagal menetapkan target: ' + xhr.status + ' ' + xhr
                            .responseText);
                    }
                });
            });

            $('#prevBtn').on('click', function() {
                if (page === 2) {
                    $('#page2').addClass('d-none');
                    $('#page1').removeClass('d-none');
                    $('#prevBtn').hide();
                    page = 1;
                }
            });

            // Conditional blocks
            $('input[name="attendance"]').on('change', function() {
                const v = $(this).val();
                $('#absenWrap').toggleClass('d-none', v !== '0');
                $('#hadirWrap').toggleClass('d-none', v !== '1');
            });
        });
    </script>
@endsection
