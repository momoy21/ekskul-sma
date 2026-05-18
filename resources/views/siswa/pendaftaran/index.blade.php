@extends('layouts.siswa')
@section('title', 'Pendaftaran Ekskul')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

{{-- Success toast untuk submit berhasil --}}
@if (session('toast_success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" id="successToast">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Berhasil!</strong> {{ session('toast_success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            const toast = document.getElementById('successToast');
            if (toast) {
                const bsAlert = new bootstrap.Alert(toast);
                bsAlert.close();
            }
        }, 5000);
    </script>
@endif

{{-- Status periode --}}
@if (! $periode)
    <div class="bg-white rounded-3 p-5 text-center shadow-sm">
        <i class="bi bi-calendar-x text-muted d-block mb-2" style="font-size:3rem"></i>
        <h6 class="fw-bold">Waktu pendaftaran belum ditentukan</h6>
        <p class="text-muted" style="font-size:.875rem">Pantau terus halaman ini ya!</p>
    </div>

@elseif (! $periode->pendaftaran_sedang_buka && ! $pendaftaran)
    <div class="bg-white rounded-3 p-5 text-center shadow-sm">
        <i class="bi bi-lock text-muted d-block mb-2" style="font-size:3rem"></i>
        <h6 class="fw-bold">Pendaftaran belum/sudah ditutup</h6>
        @if ($periode->tanggal_buka->isFuture())
            <p class="text-muted mb-0" style="font-size:.875rem">
                Dibuka pada <strong>{{ $periode->tanggal_buka->format('d/m/Y') }}</strong>
            </p>
        @else
            <p class="text-muted mb-1" style="font-size:.875rem">
                Masa pendaftaran telah berakhir pada {{ $periode->tanggal_tutup->format('d/m/Y') }}.
            </p>
            <a href="{{ route('siswa.pengumuman.index') }}" class="btn btn-sm btn-primary mt-2">
                <i class="bi bi-megaphone me-1"></i> Lihat Pengumuman
            </a>
        @endif
    </div>

@elseif ($pendaftaran && $pendaftaran->status === 'submitted')
    {{-- Sudah mendaftar (form tertutup untuk user) --}}
    <div class="bg-white rounded-3 p-5 shadow-sm text-center">
        <i class="bi bi-check-circle-fill text-success d-block mb-3" style="font-size:3rem"></i>
        <h6 class="fw-bold mb-1">Pendaftaran Berhasil!</h6>
        <p class="text-muted mb-3" style="font-size:.875rem">
            Pilihan Anda telah tersimpan. Mohon tunggu pengumuman pada tanggal
            <strong>{{ $periode->tanggal_tutup->format('d/m/Y') }} jam 11:30</strong>.
        </p>

        {{-- Tampilkan pilihan yang sudah disimpan --}}
        @if ($pilihanTersimpan && $pilihanTersimpan->isNotEmpty())
            <div class="bg-light p-3 rounded-2 mb-3">
                <p class="fw-semibold mb-2" style="font-size:.85rem">Pilihan Anda:</p>
                <ul class="list-unstyled" style="font-size:.8rem">
                    @foreach ($pilihanTersimpan as $pilihan)
                        <li class="mb-1">
                            {{ $pilihan->urutan_pilihan }}. {{ $pilihan->ekskul->nama_ekskul }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('siswa.pengumuman.index') }}" class="btn btn-primary">
            <i class="bi bi-megaphone me-1"></i> Lihat Pengumuman
        </a>
    </div>

@elseif ($pendaftaranTutup)
    {{-- Pendaftaran tutup- tampilkan pesan, jangan redirect --}}
    <div class="bg-white rounded-3 p-5 shadow-sm text-center">
        <i class="bi bi-lock text-warning d-block mb-2" style="font-size:3rem"></i>
        <h6 class="fw-bold">Pendaftaran Telah Ditutup</h6>
        <p class="text-muted mb-3" style="font-size:.875rem">
            Masa pendaftaran ekstrakurikuler telah berakhir pada
            <strong>{{ $periode->tanggal_tutup->format('d/m/Y') }} jam 11:00</strong>.
        </p>
        <p class="text-muted mb-3" style="font-size:.875rem">
            Silakan cek <strong>Pengumuman</strong> untuk melihat hasil seleksi dan melakukan pemilihan ulang jika diperlukan.
        </p>
        <a href="{{ route('siswa.pengumuman.index') }}" class="btn btn-primary">
            <i class="bi bi-megaphone me-1"></i> Lihat Pengumuman
        </a>
    </div>

@else
    {{-- Form pendaftaran aktif --}}

    {{-- Header info tahun ajaran --}}
    <div class="bg-white rounded-3 p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0">Pendaftaran Ekstrakurikuler</h6>
                <p class="text-muted mb-0" style="font-size:.82rem">
                    {{ optional($periode->tahunAjaran)->label }} · Semester {{ ucfirst($periode->semester) }}
                </p>
            </div>
            @if ($periode->pendaftaran_sedang_buka)
                <div class="countdown-box">
                <i class="bi bi-clock-fill"></i>
                <span>
                    Tutup <strong>{{ $periode->tanggal_tutup->format('d/m/Y') }} jam 11:00</strong>
                    &nbsp;·&nbsp; Sisa:
                    <strong id="countdownTimer"
                        data-target-date="{{ $periode->tanggal_tutup->format('Y-m-d') }} 11:00:00">
                        Menghitung...
                    </strong>
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Smart suggestion hasil tes --}}
    @if ($rekomendasi && $rekomendasi->isNotEmpty())
        <div class="smart-suggestion mb-4">
            <div class="smart-suggestion-title">
                <i class="bi bi-lightning-fill"></i> ✨ Hasil Rekomendasi Kamu
            </div>
            <p class="text-muted mb-3" style="font-size:.8rem">
                Pilihan di bawah sudah otomatis terisi sesuai rekomendasi. Kamu bisa mengubahnya.
            </p>
            @foreach ($rekomendasi as $r)
                <div class="suggestion-item">
                    <span class="suggestion-rank">{{ $r->emoj_peringkat }}</span>
                    <div class="flex-grow-1">
                        <span class="fw-semibold">{{ $r->ekskul->nama_ekskul }}</span>
                        <span class="text-muted" style="font-size:.78rem"> · {{ $r->ekskul->hari_pelaksanaan }}</span>
                    </div>
                    <span class="suggestion-skor">{{ $r->skor_persen }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Form pilihan ekskul --}}
    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('siswa.pendaftaran.simpan') }}" id="formPendaftaran">
        @csrf

        <div class="bg-white rounded-3 p-4 shadow-sm mb-4">
            <h6 class="fw-bold mb-3">Pilih Jumlah Ekstrakurikuler</h6>

            <div class="d-flex gap-3 mb-4">
                @for ($i = 1; $i <= 4; $i++)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jumlah_pilihan"
                               id="jumlah{{ $i }}" value="{{ $i }}"
                               {{ old('jumlah_pilihan', count($pilihanTersimpan) ?: ($rekomendasi?->count() ?: 1)) == $i ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="jumlah{{ $i }}">{{ $i }}</label>
                    </div>
                @endfor
            </div>

            <div id="dropdownPilihan">
                {{-- Dirender via JS berdasarkan jumlah_pilihan --}}
            </div>
        </div>

        {{-- E-Signature --}}
        <div class="bg-white rounded-3 p-4 shadow-sm mb-4">
            <h6 class="fw-bold mb-1">Pernyataan Persetujuan Orang Tua</h6>
            <p class="text-muted mb-3" style="font-size:.85rem">
                "Saya selaku orang tua/wali murid menyatakan setuju dengan pilihan ekstrakurikuler di atas
                dan memberikan izin kepada putra/putri saya untuk mengikuti kegiatan tersebut sesuai jadwal."
            </p>

            <label class="form-label fw-semibold">Tanda Tangan Orang Tua</label>
            <div class="signature-wrapper">
                <canvas id="signature-canvas"></canvas>
            </div>
            <div class="signature-hint">Tanda tangani di area atas</div>
            <button type="button" id="btnHapusTtd" class="btn btn-sm btn-outline-secondary mt-2">
                <i class="bi bi-trash me-1"></i> Hapus Tanda Tangan
            </button>
            <input type="hidden" id="tanda_tangan_ortu" name="tanda_tangan_ortu"
                   value="{{ old('tanda_tangan_ortu', $pendaftaran?->tanda_tangan_ortu) }}">
            @error('tanda_tangan_ortu')
                <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-check-circle me-2"></i> Simpan Pendaftaran
        </button>
    </form>
@endif

</div>
</div>
@endsection

@push('scripts')
@if (!($sudahSubmit ?? false))
<script>
    // Data ekskul dari server- digroup per hari
    const ekskulPerHari = @json($ekskulList ?? []);
    const rekomendasiIds = @json($rekomendasi ? $rekomendasi->pluck('ekskul.ekskul_id') : []);
    const pilihanTersimpan = @json($pilihanTersimpan ? $pilihanTersimpan->pluck('ekskul_id') : []);

    // Pilihan yang sudah dipilih (dari rekomendasi atau tersimpan)
    const defaultPilihan = pilihanTersimpan.length ? pilihanTersimpan : rekomendasiIds;

    function buildDropdowns(jumlah) {
        const container  = document.getElementById('dropdownPilihan');
        const hariDipakai = [];
        container.innerHTML = '';

        for (let i = 1; i <= jumlah; i++) {
            const div = document.createElement('div');
            div.className = 'mb-3';
            div.innerHTML = `
                <label class="form-label fw-semibold">Pilihan ${i}</label>
                <select name="ekskul_ids[]" class="form-select ekskul-select" data-index="${i}"
                        style="font-size:.875rem">
                    <option value="">-- Pilih Ekskul --</option>
                </select>`;
            container.appendChild(div);
        }

        renderAllDropdowns();
    }

    function renderAllDropdowns() {
        const selects     = document.querySelectorAll('.ekskul-select');
        const dipilihIds  = Array.from(selects).map(s => parseInt(s.value)).filter(Boolean);
        const hariDipakai = new Set();

        // Kumpulkan hari yang sudah terpakai
        dipilihIds.forEach(id => {
            for (const [hari, list] of Object.entries(ekskulPerHari)) {
                if (list.find(e => e.ekskul_id === id)) {
                    hariDipakai.add(hari);
                }
            }
        });

        selects.forEach((select, idx) => {
            const currentVal = parseInt(select.value) || (defaultPilihan[idx] || 0);

            // Cari hari dari ekskul yang sedang dipilih di dropdown ini
            let hariSendiri = null;
            if (currentVal) {
                for (const [hari, list] of Object.entries(ekskulPerHari)) {
                    if (list.find(e => e.ekskul_id === currentVal)) {
                        hariSendiri = hari;
                        break;
                    }
                }
            }

            // Rebuild options
            select.innerHTML = '<option value="">-- Pilih Ekskul --</option>';

            for (const [hari, list] of Object.entries(ekskulPerHari)) {
                // Blokir hari yang sudah dipakai oleh dropdown LAIN (bukan diri sendiri)
                const hariTerblokir = hariDipakai.has(hari) && hari !== hariSendiri;
                if (hariTerblokir) continue;

                const group = document.createElement('optgroup');
                group.label = `— ${hari}-`;

                list.forEach(ekskul => {
                    const opt       = document.createElement('option');
                    opt.value       = ekskul.ekskul_id;
                    opt.textContent = ekskul.nama_ekskul;
                    if (ekskul.ekskul_id === currentVal) opt.selected = true;
                    group.appendChild(opt);
                });

                select.appendChild(group);
            }
        });

        // Set default value dari rekomendasi/tersimpan
        selects.forEach((select, idx) => {
            if (! select.value && defaultPilihan[idx]) {
                select.value = defaultPilihan[idx];
            }
        });
    }

    // Rebuild saat pilihan ekskul berubah
    document.addEventListener('change', e => {
        if (e.target.classList.contains('ekskul-select')) {
            renderAllDropdowns();
        }
    });

    // Rebuild saat jumlah pilihan berubah
    document.querySelectorAll('[name="jumlah_pilihan"]').forEach(radio => {
        radio.addEventListener('change', () => buildDropdowns(parseInt(radio.value)));
    });

    // Init saat halaman load
    const jumlahAwal = document.querySelector('[name="jumlah_pilihan"]:checked')?.value || 1;
    buildDropdowns(parseInt(jumlahAwal));

    // Toast notification untuk berhasil submit
    @if (session('toast_success'))
        document.addEventListener('DOMContentLoaded', () => {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i> {{ session('toast_success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.querySelector('.toast');
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        });
    @endif
</script>
@endif
@endpush
