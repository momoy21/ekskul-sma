@extends('layouts.siswa')
@section('title', 'Hasil Rekomendasi')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

<div class="text-center mb-4">
    <div style="font-size:3rem">🎉</div>
    <h5 class="fw-bold mb-1">Hasil Rekomendasi Ekstrakurikuler Kamu</h5>
    <p class="text-muted" style="font-size:.875rem">
        Berdasarkan minat dan preferensimu, berikut 3 ekskul yang paling cocok:
    </p>
</div>

@forelse ($tes->hasilRekomendasi as $hasil)
    @php
        $rankClass = ['rekomendasi-rank-1', 'rekomendasi-rank-2', 'rekomendasi-rank-3'][$hasil->peringkat - 1] ?? '';
        $bg        = ['bg-light', 'bg-light', 'bg-light'][$hasil->peringkat - 1] ?? 'bg-white';
    @endphp

    <div class="rekomendasi-card {{ $rankClass }} bg-white mb-3">
        <div class="{{ $bg }} px-4 py-3 d-flex align-items-center gap-3">
            <span style="font-size:1.8rem">{{ $hasil->emoj_peringkat }}</span>
            <div>
                <div class="fw-bold" style="font-size:1rem">{{ $hasil->ekskul->nama_ekskul }}</div>
                <div class="text-muted" style="font-size:.8rem">
                    {{ $hasil->ekskul->kategori->nama_kategori }} ·
                    {{ $hasil->ekskul->nama_pembina }} ·
                    {{ $hasil->ekskul->hari_pelaksanaan }}
                </div>
            </div>
            <div class="ms-auto text-end">
                <div class="fw-bold text-primary" style="font-size:1.2rem">{{ $hasil->skor_persen }}</div>
                <div class="text-muted" style="font-size:.72rem">Skor Kecocokan</div>
            </div>
        </div>

        <div class="px-4 py-3">
            {{-- Progress bar skor --}}
            <div class="skor-bar-wrapper mb-3">
                <div class="skor-bar-fill" style="width:{{ $hasil->skor_persen }}"></div>
            </div>

            <div class="row g-2" style="font-size:.83rem">
                <div class="col-sm-4">
                    <span class="text-muted">Lokasi:</span>
                    <strong>{{ $hasil->ekskul->lokasi }}</strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted">Biaya:</span>
                    <strong>{{ $hasil->ekskul->label_biaya }}</strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted">Fasilitas:</span>
                    <span>{{ $hasil->ekskul->label_fasilitas }}</span>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="bg-white rounded-3 p-5 text-center text-muted shadow-sm">
        <i class="bi bi-exclamation-circle d-block mb-2" style="font-size:2.5rem"></i>
        Hasil rekomendasi tidak ditemukan. Silakan ikuti tes terlebih dahulu.
    </div>
@endforelse

<div class="alert alert-info d-flex gap-2 mt-3" style="font-size:.85rem">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    Hasil rekomendasi ini akan otomatis muncul sebagai saran saat kamu mendaftar ekskul.
</div>

<div class="d-flex gap-2 mt-3">
    <a href="{{ route('siswa.informasi-ekskul.index') }}" class="btn btn-outline-secondary px-4">
        <i class="bi bi-list-ul me-1"></i> Lihat Semua Ekskul
    </a>
    <a href="{{ route('siswa.pendaftaran.index') }}" class="btn btn-primary px-4">
        <i class="bi bi-pencil-square me-1"></i> Pergi ke Pendaftaran
    </a>
</div>

</div>
</div>
@endsection
