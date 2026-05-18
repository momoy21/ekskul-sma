@extends('layouts.siswa')
@section('title', 'Pengumuman')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

@if ($fase === 'belum')
    {{-- Belum ada pengumuman --}}
    <div class="bg-white rounded-3 p-5 text-center shadow-sm">
        <i class="bi bi-calendar-x text-muted d-block mb-2" style="font-size:3rem"></i>
        <h6 class="fw-bold">Pengumuman belum tersedia</h6>
        <p class="text-muted" style="font-size:.875rem">
            @if ($periode)
                Pengumuman akan tampil pada
                <strong>{{ $periode->tanggal_tutup->format('d/m/Y') }}</strong>.
            @else
                Belum ada periode pendaftaran yang aktif.
            @endif
        </p>
    </div>

@elseif ($fase === 'selesai')
    {{-- Pemilihan ulang selesai- tampilkan hasil final --}}
    <div class="bg-white rounded-3 p-4 shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size:1.5rem"></i>
            <div>
                <h6 class="fw-bold mb-0">Masa pemilihan ulang telah selesai</h6>
                <p class="text-muted mb-0" style="font-size:.82rem">Berikut adalah pilihan final kamu.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th>Nama Ekskul</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pilihan as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->ekskulFinal?->nama_ekskul ?? $p->ekskul->nama_ekskul }}</td>
                            <td class="text-center">
                                <span class="zona-badge hijau">🟢 Diterima</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-3">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('siswa.dashboard') }}" class="btn btn-outline-primary">
        <i class="bi bi-house me-1"></i> Kembali ke Dashboard
    </a>

@else
    {{-- Fase pengumuman- tampilkan zona + form pemilihan ulang --}}

    {{-- Header --}}
    <div class="bg-white rounded-3 p-4 shadow-sm mb-4">
        <h5 class="fw-bold mb-1">📣 Pengumuman Ekstrakurikuler</h5>
        <p class="text-muted mb-0" style="font-size:.83rem">
            {{ optional($periode->tahunAjaran)->label }} · Semester {{ ucfirst($periode->semester) }}
        </p>
        <p class="text-muted mb-0" style="font-size:.83rem">
            Pengumuman tersedia sejak {{ $periode->tanggal_tutup->format('d/m/Y') }}
            @if ($periode->tanggal_pemilihan_ulang)
                · Pemilihan ulang hingga {{ $periode->tanggal_pemilihan_ulang->format('d/m/Y') }}
            @endif
        </p>
    </div>

    {{-- Notifikasi auto-finalized --}}
    @if ($semuaZonaHijau)
        <div class="alert alert-success py-3 mb-4 d-flex gap-2" style="font-size:.85rem">
            <i class="bi bi-check-circle-fill flex-shrink-0 mt-1"></i>
            <div>
                <strong>Selamat!</strong> Semua pilihan ekskul kamu diterima (zona hijau).
                Pilihan ini telah otomatis disimpan sebagai final. Tidak ada perubahan yang perlu dilakukan.
            </div>
        </div>
    @endif

    {{-- Panduan zona --}}
    <div class="bg-white rounded-3 p-4 shadow-sm mb-4">
        <h6 class="fw-bold mb-3">Panduan Status Zona</h6>
        <div class="d-flex flex-column gap-2" style="font-size:.85rem">
            <div class="zona-row hijau d-flex align-items-start gap-2">
                <span class="zona-badge hijau flex-shrink-0">🟢 Zona Hijau</span>
                <span>Diterima. Tidak diperlukan tindakan lebih lanjut.</span>
            </div>
            <div class="zona-row kuning d-flex align-items-start gap-2">
                <span class="zona-badge kuning flex-shrink-0">🟡 Zona Kuning</span>
                <span>Kuota hampir terpenuhi. Wajib tentukan 1 pilihan cadangan. Jika ekskul utama tidak dibuka, sistem otomatis memindahkan ke cadangan.</span>
            </div>
            <div class="zona-row merah d-flex align-items-start gap-2">
                <span class="zona-badge merah flex-shrink-0">🔴 Zona Merah</span>
                <span>Kuota tidak terpenuhi, ekskul tidak akan dibuka. Wajib ganti atau hapus pilihan ini.</span>
            </div>
        </div>
    </div>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    @if (! $pendaftaran)
        <div class="bg-white rounded-3 p-4 shadow-sm text-center text-muted">
            <i class="bi bi-info-circle d-block mb-2" style="font-size:2rem"></i>
            Kamu belum melakukan pendaftaran ekskul.
        </div>
    @else
        <form method="POST" action="{{ route('siswa.pengumuman.simpan-final') }}" id="formFinal">
        @csrf

        {{-- Ringkasan pilihan --}}
        <div class="bg-white rounded-3 p-4 shadow-sm mb-4">
            <h6 class="fw-bold mb-3">Ringkasan Pilihan Kamu</h6>

            @foreach ($pilihan as $p)
                @php
                    $zona  = $p->status_zona ?? 'merah';
                    $idStr = (string) $p->pilihan_id;
                @endphp

                <div class="zona-row {{ $zona }} mb-2" id="row_{{ $p->pilihan_id }}">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $p->ekskul->nama_ekskul }}</div>
                            <div class="text-muted" style="font-size:.78rem">
                                {{ $p->ekskul->hari_pelaksanaan }} · {{ $p->ekskul->nama_pembina }}
                            </div>
                        </div>
                        <span class="zona-badge {{ $zona }}">
                            @if ($zona === 'hijau') 🟢 Zona Hijau- Diterima
                            @elseif ($zona === 'kuning') 🟡 Zona Kuning
                            @else 🔴 Zona Merah @endif
                        </span>
                    </div>

                    {{-- Aksi sesuai zona --}}
                    @if ($zona === 'kuning')
                        <div class="mt-3">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">
                                Pilih Ekskul Cadangan <span class="text-danger">*</span>
                            </label>
                            <select name="cadangan[{{ $p->pilihan_id }}]"
                                    class="form-select form-select-sm cadangan-select"
                                    style="max-width:280px">
                                <option value="">-- Pilih Cadangan --</option>
                                @foreach ($ekskulPengganti as $hari => $list)
                                    <optgroup label="{{ $hari }}">
                                        @foreach ($list as $e)
                                            <option value="{{ $e->ekskul_id }}"
                                                {{ old("cadangan.{$p->pilihan_id}", $p->ekskul_cadangan_id) == $e->ekskul_id ? 'selected' : '' }}>
                                                {{ $e->nama_ekskul }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                    @elseif ($zona === 'merah')
                        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                            <div>
                                <label class="form-label mb-1" style="font-size:.82rem;font-weight:600">
                                    Ganti dengan ekskul lain:
                                </label>
                                <select name="pengganti[{{ $p->pilihan_id }}]"
                                        class="form-select form-select-sm pengganti-select"
                                        style="max-width:250px"
                                        id="pengganti_{{ $p->pilihan_id }}">
                                    <option value="">-- Pilih Pengganti --</option>
                                    @foreach ($ekskulPengganti as $hari => $list)
                                        <optgroup label="{{ $hari }}">
                                            @foreach ($list as $e)
                                                <option value="{{ $e->ekskul_id }}"
                                                    {{ old("pengganti.{$p->pilihan_id}") == $e->ekskul_id ? 'selected' : '' }}>
                                                    {{ $e->nama_ekskul }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Hapus (disabled jika hanya 1 pilihan) --}}
                            @php $jumlahAktif = $pilihan->count(); @endphp
                            @if ($jumlahAktif > 1)
                                <div>
                                    <label class="form-label mb-1 d-block" style="font-size:.82rem;font-weight:600">Atau hapus:</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input hapus-cb"
                                               name="hapus[]" value="{{ $p->pilihan_id }}"
                                               id="hapus_{{ $p->pilihan_id }}"
                                               {{ in_array($p->pilihan_id, old('hapus', [])) ? 'checked' : '' }}
                                               onchange="toggleHapus({{ $p->pilihan_id }}, this.checked)">
                                        <label class="form-check-label text-danger" for="hapus_{{ $p->pilihan_id }}"
                                               style="font-size:.83rem">
                                            Hapus pilihan ini
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted" style="font-size:.78rem">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Tidak bisa dihapus (minimal 1 pilihan).
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Validasi info --}}
            <div id="validasiInfo" class="mt-3"></div>
        </div>

        {{-- E-Sign- hanya muncul jika ada zona merah ATAU ada input perubahan merah yang belum disimpan --}}
        @php
            $adaZonaMerah = $pilihan->where('status_zona', 'merah')->isNotEmpty();
            $adaInputPengganti = ! empty(old('pengganti', []));
            $adaInputHapus = ! empty(old('hapus', []));
            $tampilkanTtd = $adaZonaMerah || $adaInputPengganti || $adaInputHapus;
        @endphp
        @if ($tampilkanTtd)
            <div class="bg-white rounded-3 p-4 shadow-sm mb-4" id="sectionTtd">
                <h6 class="fw-bold mb-1">Pernyataan Persetujuan Orang Tua</h6>
                <p class="text-muted mb-3" style="font-size:.85rem">
                    "Saya selaku orang tua/wali murid memahami bahwa pilihan sebelumnya tidak dapat dilaksanakan
                    karena kuota tidak terpenuhi dan menyetujui perubahan pilihan tersebut."
                </p>
                <div class="signature-wrapper">
                    <canvas id="signature-canvas"></canvas>
                </div>
                <div class="signature-hint">Tanda tangani di area atas</div>
                <button type="button" id="btnHapusTtd" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-trash me-1"></i> Hapus Tanda Tangan
                </button>
                <input type="hidden" id="tanda_tangan_ortu" name="tanda_tangan_ortu">
                @error('tanda_tangan_ortu')
                    <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <button type="submit" class="btn btn-primary btn-lg w-100" id="btnSimpanFinal"
                @if ($semuaZonaHijau) style="display:none;" @endif>
            <i class="bi bi-check-circle me-2"></i> Simpan Final
        </button>

        </form>
    @endif
@endif

</div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle tampilan pengganti saat checkbox hapus dicentang
    function toggleHapus(pilihanId, dihapus) {
        const row      = document.getElementById(`row_${pilihanId}`);
        const pengganti = document.getElementById(`pengganti_${pilihanId}`);

        if (dihapus) {
            row.classList.add('hapus');
            if (pengganti) pengganti.disabled = true;
        } else {
            row.classList.remove('hapus');
            if (pengganti) pengganti.disabled = false;
        }
    }

    // Inisialisasi state hapus yang sudah tercentang (dari old input)
    document.querySelectorAll('.hapus-cb:checked').forEach(cb => {
        const id = cb.value;
        toggleHapus(id, true);
    });

    // Prevent double-click dan hide button setelah submit berhasil
    const formFinal = document.getElementById('formFinal');
    if (formFinal) {
        formFinal.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnSimpanFinal');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Menyimpan...';
            }
        });
    }

    // Monitor untuk toast success dan hide button
    document.addEventListener('DOMContentLoaded', function() {
        // Cek apakah ada toast success di halaman (dari Toastr atau notifikasi lainnya)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                // Jika ada element dengan class success/alert-success di DOM
                if (document.querySelector('.alert-success, .toast-success, [class*="success"]')) {
                    const btn = document.getElementById('btnSimpanFinal');
                    if (btn && !btn.style.display || btn.style.display !== 'none') {
                        // Tunggu 1 detik biar toast terlihat dulu
                        setTimeout(function() {
                            btn.style.display = 'none';
                        }, 1000);
                    }
                }
            });
        });

        // Start observing the document for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true
        });
    });
</script>
@endpush
