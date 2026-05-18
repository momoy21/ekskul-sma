@extends('layouts.app')
@section('title', 'Timeline Pendaftaran')

@section('content')

<div class="mb-3">
    <a href="{{ route('admin.pendaftaran.hasil') }}" class="btn-primary btn-sm">
        <i class="bi bi-file-earmark-text me-1"></i> Lihat Hasil Pendaftaran
    </a>
</div>

<div class="row g-4">
    {{-- Form set timeline --}}
    <div class="col-lg-6">
        <div class="form-card">
            <h6 class="fw-bold mb-1">Pengaturan Timeline Pendaftaran</h6>
            <p class="text-muted mb-3" style="font-size:.83rem">
                Tahun Ajaran: <strong>{{ $tahunAktif ? $tahunAktif->label : '—' }}</strong>
                &nbsp;·&nbsp;
                Semester: <strong>{{ $tahunAktif ? ucfirst($tahunAktif->semester) : '—' }}</strong>
            </p>

            {{-- Info jam hardcode --}}
            <div class="alert alert-info py-2 mb-4 d-flex gap-2" style="font-size:.82rem">
                <i class="bi bi-clock-fill flex-shrink-0 mt-1"></i>
                <div>
                    <strong>Jam yang sudah ditetapkan sistem:</strong><br>
                    🔒 Pendaftaran ditutup jam <strong>11:00</strong><br>
                    📣 Pengumuman + pemilihan ulang dibuka jam <strong>11:30</strong>
                    (di hari yang sama dengan tutup)<br>
                    🔒 Pemilihan ulang ditutup jam <strong>23:59</strong>
                    di tanggal batas akhir yang Anda isi
                </div>
            </div>

            @if (! $tahunAktif)
                <div class="alert alert-warning py-2" style="font-size:.85rem">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Belum ada tahun ajaran aktif. Buat tahun ajaran terlebih dahulu.
                </div>
            @else
                @if ($errors->any())
                    <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
                        <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.pendaftaran.simpan') }}">
                    @csrf

                    {{-- Tanggal Buka --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Tanggal Buka Pendaftaran <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal_buka"
                               class="form-control @error('tanggal_buka') is-invalid @enderror"
                               value="{{ old('tanggal_buka', $periode?->tanggal_buka?->format('Y-m-d')) }}"
                               id="tglBuka">
                        @error('tanggal_buka')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Pendaftaran dibuka mulai jam 00:00 di tanggal ini.</div>
                    </div>

                    {{-- Tanggal Tutup --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Tanggal Tutup Pendaftaran <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal_tutup"
                               class="form-control @error('tanggal_tutup') is-invalid @enderror"
                               value="{{ old('tanggal_tutup', $periode?->tanggal_tutup?->format('Y-m-d')) }}"
                               id="tglTutup">
                        @error('tanggal_tutup')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        {{-- Info durasi + jam tutup + jam pengumuman --}}
                        <div class="form-text" id="infoTutup">
                            Pendaftaran ditutup jam <strong>11:00</strong>.
                            Pengumuman otomatis tampil jam <strong>11:30</strong> di hari yang sama.
                        </div>
                        <div class="text-primary fw-semibold mt-1" id="durasiInfo" style="font-size:.8rem"></div>
                    </div>

                    {{-- Batas Akhir Pemilihan Ulang --}}
                    <div class="mb-4">
                        <label class="form-label">
                            Batas Akhir Pemilihan Ulang <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal_pemilihan_ulang"
                               class="form-control @error('tanggal_pemilihan_ulang') is-invalid @enderror"
                               value="{{ old('tanggal_pemilihan_ulang', $periode?->tanggal_pemilihan_ulang?->format('Y-m-d')) }}"
                               id="tglPemilihan">
                        @error('tanggal_pemilihan_ulang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Pemilihan ulang dibuka jam <strong>11:30</strong> di hari pengumuman
                            dan berakhir jam <strong>23:59</strong> di tanggal ini.
                        </div>
                    </div>

                    {{-- Ringkasan timeline --}}
                    <div class="bg-light rounded-3 p-3 mb-4" id="ringkasanTimeline" style="display:none;font-size:.82rem">
                        <div class="fw-semibold mb-2 text-muted">Ringkasan Timeline:</div>
                        <div id="isiRingkasan"></div>
                    </div>

                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Timeline
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Ringkasan kuota per ekskul --}}
    <div class="col-lg-6">
        <div class="form-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-0">Ringkasan Pendaftar</h6>
                    <p class="text-muted mb-0" style="font-size:.8rem">Jumlah pendaftar aktif per ekskul</p>
                </div>
            </div>

            {{-- Legenda zona --}}
            @if ($periode)
                <div class="d-flex gap-3 mb-3" style="font-size:.78rem">
                    <span><span class="zona-badge hijau">🟢 Hijau</span> &nbsp;≥ kuota</span>
                    <span><span class="zona-badge kuning">🟡 Kuning</span> &nbsp;= kuota-1</span>
                    <span><span class="zona-badge merah">🔴 Merah</span> &nbsp;< kuota-1</span>
                </div>
            @endif

            @if (! $periode || empty($ringkasan))
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-bar-chart d-block mb-2" style="font-size:2rem"></i>
                    <p style="font-size:.85rem">
                        {{ ! $periode ? 'Timeline belum diatur.' : 'Belum ada pendaftar.' }}
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:.83rem">
                        <thead class="table-light">
                            <tr>
                                <th>Ekskul</th>
                                <th class="text-center">Pendaftar / Kuota</th>
                                <th class="text-center">Zona</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ringkasan as $ekskulId => $data)
                                @php $ekskul = \App\Models\Ekskul::find($ekskulId); @endphp
                                @if ($ekskul)
                                    <tr>
                                        <td>{{ $ekskul->nama_ekskul }}</td>
                                        <td class="text-center fw-semibold">
                                            {{ $data['jumlah'] }} / {{ $ekskul->kuota_minimal }}
                                        </td>
                                        <td class="text-center">
                                            <span class="zona-badge {{ $data['zona'] }}">
                                                @if ($data['zona'] === 'hijau') 🟢 Hijau
                                                @elseif ($data['zona'] === 'kuning') 🟡 Kuning
                                                @else 🔴 Merah @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Hitung dan tampilkan ringkasan timeline saat tanggal berubah
    function updateRingkasan() {
        const buka      = document.getElementById('tglBuka')?.value;
        const tutup     = document.getElementById('tglTutup')?.value;
        const pemilihan = document.getElementById('tglPemilihan')?.value;
        const durasiEl  = document.getElementById('durasiInfo');
        const ringkasan = document.getElementById('ringkasanTimeline');
        const isi       = document.getElementById('isiRingkasan');

        if (buka && tutup) {
            const tBuka  = new Date(buka);
            const tTutup = new Date(tutup);
            const durasi = Math.ceil((tTutup - tBuka) / (1000*60*60*24)) + 1;

            if (durasi > 0) {
                durasiEl.textContent = `Masa pendaftaran: ${durasi} hari`;
            } else {
                durasiEl.textContent = '';
            }
        }

        if (buka && tutup && pemilihan) {
            const fmt = d => new Date(d).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});

            isi.innerHTML = `
                <div class="d-flex flex-column gap-1">
                    <div>📝 <strong>Pendaftaran buka</strong>: ${fmt(buka)} jam 00:00</div>
                    <div>🔒 <strong>Pendaftaran tutup</strong>: ${fmt(tutup)} jam 11:00</div>
                    <div>📣 <strong>Pengumuman tampil</strong>: ${fmt(tutup)} jam 11:30</div>
                    <div>🔄 <strong>Pemilihan ulang</strong>: ${fmt(tutup)} jam 11:30 – ${fmt(pemilihan)} jam 23:59</div>
                </div>`;
            ringkasan.style.display = 'block';
        } else {
            ringkasan.style.display = 'none';
        }
    }

    ['tglBuka', 'tglTutup', 'tglPemilihan'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', updateRingkasan);
    });

    // Trigger saat halaman load kalau sudah ada value
    updateRingkasan();
</script>
@endpush
