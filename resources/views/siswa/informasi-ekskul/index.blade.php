@extends('layouts.siswa')
@section('title', 'Informasi Ekskul')

@section('content')

{{-- Hero: Manfaat & Pramuka- rata tengah --}}
<div class="info-hero-card">

    <div class="hero-eyebrow">
        <i class="bi bi-stars"></i> Kenapa Harus Ikut Ekskul?
    </div>

    <h5>Lebih dari Sekadar Kegiatan Setelah Pulang Sekolah</h5>

    <p class="hero-lead">
        Ekskul adalah tempat kamu berkembang, menemukan passion, dan menambah pengalaman seru selama masa SMA.
        Bukan cuma soal kegiatan setelah sekolah, tapi juga tentang belajar kerja sama, menambah teman,
        melatih percaya diri, dan menemukan versi terbaik dari dirimu.
    </p>

    {{-- Benefit pills --}}
    <div class="hero-benefits">
        <span class="hero-benefit-pill"><i class="bi bi-lightbulb-fill"></i> Temukan Minat Baru</span>
        <span class="hero-benefit-pill"><i class="bi bi-people-fill"></i> Bangun Koneksi</span>
        <span class="hero-benefit-pill"><i class="bi bi-trophy-fill"></i> Raih Prestasi</span>
        <span class="hero-benefit-pill"><i class="bi bi-graph-up-arrow"></i> Tingkatkan Percaya Diri</span>
        <span class="hero-benefit-pill"><i class="bi bi-briefcase-fill"></i> Persiapan Masa Depan</span>
    </div>

    <div class="info-pramuka-alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div>
            <strong>Wajib Diketahui!</strong><br>
            Ekstrakurikuler <strong>Pramuka</strong> bersifat <strong>WAJIB</strong>
            untuk seluruh siswa, dilaksanakan setiap <strong>Rabu</strong>.<br>
            Seluruh kegiatan ekskul berlangsung pukul <strong>15.00 – 16.00 WIB</strong>.
        </div>
    </div>
</div>

{{-- Timeline pendaftaran --}}
@if ($periode)
<div class="info-timeline-card">
    <h6>📅 Jadwal Pendaftaran Semester {{ ucfirst($periode->semester) }} {{ $tahunAktif->label }}</h6>

    <div class="htimeline">

        <div class="htimeline-item">
            <div class="htimeline-label">📝 Buka Pendaftaran</div>
            <div class="htimeline-date">{{ $periode->tanggal_buka->format('d M Y') }}</div>
        </div>

        <div class="htimeline-item">
            <div class="htimeline-label">📣 Tutup Pendaftaran & Pengumuman</div>
            <div class="htimeline-date">{{ $periode->tanggal_tutup->format('d M Y') }}</div>
            <div class="htimeline-sub">Tutup pendaftaran pukul 11:00 &nbsp;·&nbsp; Pengumuman pukul 11:30</div>
        </div>

        @if ($periode->tanggal_pemilihan_ulang)
            <div class="htimeline-item">
                <div class="htimeline-label">🔄 Pemilihan Ulang</div>
                <div class="htimeline-date">
                    {{ $periode->tanggal_tutup->format('d M') }} –
                    {{ $periode->tanggal_pemilihan_ulang->format('d M Y') }}
                </div>
                <div class="htimeline-sub">s/d pukul 23:59</div>
            </div>
        @endif

    </div>
</div>
@endif

{{-- Grid katalog ekskul --}}
<div class="mb-5">
    <div class="info-section-title">
        <i class="bi bi-trophy text-warning"></i>
        Daftar Ekstrakurikuler
    </div>

    @if ($ekskulList->isEmpty())
        <div class="bg-white rounded-3 p-5 text-center text-muted shadow-sm" style="border:1px solid var(--border-color)">
            <i class="bi bi-trophy d-block mb-3" style="font-size:2.5rem;color:var(--text-placeholder)"></i>
            <p class="mb-0" style="font-size:.875rem">Belum ada ekstrakurikuler yang tersedia saat ini.</p>
        </div>
    @else
        <div class="ekskul-grid-container">
            <div class="ekskul-grid">
                @foreach ($ekskulList as $ekskul)
                    @php
                        $kuota = $dotKuota[$ekskul->ekskul_id] ?? ['jumlah' => 0, 'warna' => 'merah'];
                    @endphp
                    <div class="ekskul-card" onclick="bukaDetailEkskul({{ $ekskul->ekskul_id }})">
                        <img src="{{ $ekskul->foto_url }}" alt="{{ $ekskul->nama_ekskul }}"
                             class="ekskul-card-img">
                        <div class="ekskul-card-body">
                            <div class="ekskul-card-title">{{ $ekskul->nama_ekskul }}</div>
                            <div class="ekskul-card-meta">
                                <span><i class="bi bi-calendar3"></i> {{ $ekskul->hari_pelaksanaan }}</span>
                                <span>·</span>
                                <span><i class="bi bi-person"></i> {{ $ekskul->nama_pembina }}</span>
                            </div>
                            <div class="ekskul-card-meta">
                                <span class="badge bg-light text-dark border" style="font-size:.7rem">
                                    {{ $ekskul->kategori->nama_kategori }}
                                </span>
                                <span class="kuota-dot {{ $kuota['warna'] }} ms-auto">
                                    {{ $kuota['jumlah'] }} / {{ $ekskul->kuota_minimal }}
                                </span>
                            </div>
                            <p class="ekskul-card-desc mt-1">{{ $ekskul->deskripsi_kegiatan }}</p>
                            <div class="ekskul-card-labels mt-2">
                                <div style="font-size:.75rem;color:#6b7280"><strong>Biaya:</strong> {{ $ekskul->label_biaya }}</div>
                                <div style="font-size:.75rem;color:#6b7280"><strong>Fasilitas:</strong> {{ $ekskul->label_fasilitas }}</div>
                                <div style="font-size:.75rem;color:#6b7280"><strong>Intensitas:</strong> {{ $ekskul->label_intensitas }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- CTA Rekomendasi- rata tengah, dark card --}}
<div class="info-cta-card">

    <h6>🎯 Bingung Pilih Ekskul yang Tepat?</h6>
    <p>
        Biarkan sistem kami yang bantu! Jawab beberapa pertanyaan singkat
        dan sistem akan menganalisis minat, gaya belajar, dan preferensimu.
        Lalu sistem akan merekomendasikan 3 ekskul yang paling cocok untukmu. ✨
    </p>

    <div class="info-cta-steps">
        <div class="info-cta-step">
            <div class="info-cta-step-icon"><i class="bi bi-patch-question"></i></div>
            <div class="info-cta-step-title">Jawab Soal</div>
            <div class="info-cta-step-desc">Pilih angka 1–5 sesuai tingkat minat & preferensimu</div>
        </div>
        <i class="bi bi-chevron-right info-cta-step-arrow"></i>
        <div class="info-cta-step">
            <div class="info-cta-step-icon"><i class="bi bi-cpu"></i></div>
            <div class="info-cta-step-title">Sistem Analisis</div>
            <div class="info-cta-step-desc">Sistem menghitung skor kecocokan secara otomatis</div>
        </div>
        <i class="bi bi-chevron-right info-cta-step-arrow"></i>
        <div class="info-cta-step">
            <div class="info-cta-step-icon"><i class="bi bi-trophy"></i></div>
            <div class="info-cta-step-title">Lihat Hasil</div>
            <div class="info-cta-step-desc">Dapatkan 3 rekomendasi ekskul terbaik, siap untuk didaftar</div>
        </div>
    </div>

    <a href="{{ route('siswa.tes.index') }}" class="btn btn-primary px-5 py-2">
        <i class="bi bi-arrow-right-circle me-2"></i> Ayo, Mulai Tes Rekomendasi!
    </a>
</div>

{{-- Modal Detail Ekskul --}}
<div class="modal fade" id="modalDetailEkskul" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);border:1px solid var(--border-color);overflow:hidden">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal" style="z-index:10"></button>
            <div id="modalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modal = new bootstrap.Modal(document.getElementById('modalDetailEkskul'));

    async function bukaDetailEkskul(ekskulId) {
        document.getElementById('modalBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>`;
        modal.show();

        try {
            const res  = await fetch(`/siswa/informasi-ekskul/${ekskulId}/detail`);
            const data = await res.json();

            const warnaDot = data.kuota.warna;
            const dotEmoji = warnaDot === 'hijau' ? '🟢' : warnaDot === 'kuning' ? '🟡' : '🔴';

            const labelBiaya = {
                'Tidak Ada Biaya (Rp 0)': 'Tidak Ada Biaya (Rp 0)',
                'Sedikit Biaya (Rp 1.000 - Rp 100.000)': 'Sedikit Biaya (Rp 1.000 - Rp 100.000)',
                'Terjangkau (Rp 101.000 - Rp 200.000)': 'Terjangkau (Rp 101.000 - Rp 200.000)',
                'Sedikit Mahal (Rp 201.000 - Rp 300.000)': 'Sedikit Mahal (Rp 201.000 - Rp 300.000)',
                'Mahal (Rp 301.000+)': 'Mahal (Rp 301.000+)',
            };

            const labelFasilitas = {
                'Seluruhnya dibawa sendiri': 'Seluruhnya dibawa sendiri',
                'Beberapa dari sekolah, lebih banyak dibawa sendiri': 'Beberapa dari sekolah, lebih banyak dibawa sendiri',
                'Sebagian disediakan sekolah, sebagian sendiri': 'Sebagian disediakan sekolah, sebagian sendiri',
                'Beberapa dari sekolah, lebih banyak disediakan sekolah': 'Beberapa dari sekolah, lebih banyak disediakan sekolah',
                'Dari semua disediakan sekolah': 'Dari semua disediakan sekolah',
            };

            const labelIntensitas = {
                'Intensitas Sangat Tinggi': 'Sangat Tinggi',
                'Intensitas Tinggi': 'Tinggi',
                'Intensitas Sedang': 'Sedang',
                'Intensitas Rendah': 'Rendah',
                'Intensitas Sangat Rendah': 'Sangat Rendah',
            };

            document.getElementById('modalBody').innerHTML = `
                <img src="${data.foto_url}" alt="${data.nama_ekskul}"
                     style="width:100%;max-height:240px;object-fit:cover">
                <div class="p-4">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">${data.nama_ekskul}</h5>
                            <span class="badge bg-primary-subtle text-primary">${data.nama_kategori}</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3" style="font-size:.875rem">
                        <div class="col-sm-6">
                            <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700">Pembina</div>
                            <div class="fw-semibold">${data.nama_pembina}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700">Jadwal</div>
                            <div class="fw-semibold">${data.hari_pelaksanaan} · 15.00 – 16.00</div>
                        </div>
                    </div>

                    <div class="mb-3" style="border-top: 1px solid #e5e7eb; padding-top: 1rem">
                        <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700">Informasi Kegiatan</div>
                        <div class="row g-3" style="font-size:.875rem">
                            <div class="col-12">
                                <div class="text-muted mb-1" style="font-size:.8rem">Biaya</div>
                                <div class="fw-semibold">${labelBiaya[data.label_biaya] || data.label_biaya}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted mb-1" style="font-size:.8rem">Fasilitas</div>
                                <div class="fw-semibold">${labelFasilitas[data.label_fasilitas] || data.label_fasilitas}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted mb-1" style="font-size:.8rem">Intensitas Kegiatan</div>
                                <div class="fw-semibold">${labelIntensitas[data.label_intensitas] || data.label_intensitas}</div>
                            </div>
                        </div>
                    </div>

                    ${data.deskripsi_kegiatan ? `
                        <div class="mb-3">
                            <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700">Deskripsi Kegiatan</div>
                            <p style="font-size:.875rem;line-height:1.7;color:#374151">${data.deskripsi_kegiatan}</p>
                        </div>` : ''}

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span style="font-size:.85rem">
                            <i class="bi bi-geo-alt-fill text-muted me-1"></i>
                            <strong>${data.lokasi}</strong>
                        </span>
                        <span class="kuota-dot ${warnaDot} fw-semibold" style="font-size:.85rem">
                            ${dotEmoji} ${data.kuota.jumlah} / ${data.kuota.minimal} pendaftar
                        </span>
                    </div>
                </div>`;
        } catch (err) {
            document.getElementById('modalBody').innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-circle d-block mb-2" style="font-size:2rem"></i>
                    Gagal memuat data. Silakan coba lagi.
                </div>`;
        }
    }
</script>
@endpush
