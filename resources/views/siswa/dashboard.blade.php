@extends('layouts.siswa')
@section('title', 'Dashboard')

@section('content')

{{-- Greeting --}}
<div class="dashboard-greeting">
    <h5>Halo, {{ session('nama') }} 👋</h5>
    <p>Kelas {{ session('label_kelas') }} &nbsp;·&nbsp; NISN {{ session('nisn') }}</p>
</div>

{{-- Status cards --}}
<div class="dashboard-status-row">

    {{-- Tahun ajaran aktif --}}
    <div class="dashboard-status-card">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="status-icon-wrap primary">
                <i class="bi bi-calendar3"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size:.9rem">{{ $tahunAktif ? $tahunAktif->label : '—' }}</div>
                <div class="text-muted" style="font-size:.78rem">Tahun Ajaran Aktif</div>
            </div>
        </div>
        @if ($tahunAktif)
            <span class="badge bg-primary-subtle text-primary" style="font-size:.75rem">
                Semester {{ ucfirst($tahunAktif->semester) }}
            </span>
        @endif
    </div>

    {{-- Status tes --}}
    <div class="dashboard-status-card">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="status-icon-wrap {{ $sudahTes ? 'success' : 'warning' }}">
                <i class="bi bi-patch-{{ $sudahTes ? 'check' : 'question' }}"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size:.9rem">Tes Rekomendasi</div>
                <div class="text-muted" style="font-size:.78rem">
                    {{ $sudahTes ? 'Sudah selesai' : 'Belum diikuti' }}
                </div>
            </div>
        </div>
        <a href="{{ route('siswa.tes.index') }}"
           class="btn btn-sm {{ $sudahTes ? 'btn-outline-success' : 'btn-warning' }}">
            {{ $sudahTes ? 'Lihat Hasil' : 'Mulai Tes' }}
        </a>
    </div>

    {{-- Status pendaftaran --}}
    <div class="dashboard-status-card">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="status-icon-wrap {{ $sudahDaftar ? 'success' : 'secondary' }}">
                <i class="bi bi-pencil-{{ $sudahDaftar ? 'fill' : 'square' }}"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size:.9rem">Pendaftaran Ekskul</div>
                <div class="text-muted" style="font-size:.78rem">
                    {{ $sudahDaftar ? 'Sudah mendaftar' : ($periode?->pendaftaran_sedang_buka ? 'Segera daftar!' : 'Belum dibuka') }}
                </div>
            </div>
        </div>
        <a href="{{ route('siswa.pendaftaran.index') }}"
           class="btn btn-sm {{ $sudahDaftar ? 'btn-outline-success' : ($periode?->pendaftaran_sedang_buka ? 'btn-primary' : 'btn-outline-secondary') }}">
            {{ $sudahDaftar ? 'Lihat Pilihan' : 'Ke Pendaftaran' }}
        </a>
    </div>
</div>

{{-- Timeline pendaftaran --}}
@if ($periode)
<div class="dashboard-timeline-card">
    <h6>
        <i class="bi bi-calendar-event text-primary"></i>
        Jadwal Pendaftaran Semester {{ ucfirst($periode->semester) }} {{ $periode->tahunAjaran->label }}
    </h6>

    <div class="htimeline">
        <div class="htimeline-item">
            <div class="htimeline-label">📝 Pendaftaran</div>
            <div class="htimeline-date">
                {{ $periode->tanggal_buka->format('d M Y') }}
            </div>
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
                    {{ $periode->tanggal_tutup->format('d M Y') }} –
                    {{ $periode->tanggal_pemilihan_ulang->format('d M Y') }}
                </div>
                <div class="htimeline-sub">s/d pukul 23:59</div>
            </div>
        @endif
    </div>

    @if ($periode->pendaftaran_sedang_buka)
        <div class="dashboard-countdown-alert">
            <i class="bi bi-clock-fill flex-shrink-0"></i>
            <span>Pendaftaran sedang buka! Tutup pada
                <strong>{{ $periode->tanggal_tutup->format('d/m/Y') }} jam 11:00</strong>.
                Sisa waktu: <strong id="countdownTimer"
                    data-target-date="{{ $periode->tanggal_tutup->format('Y-m-d') }} 11:00:00">
                    Menghitung...
                </strong>
            </span>
        </div>
    @endif
</div>
@endif

{{-- Shortcut menu --}}
<div class="dashboard-shortcut-card">
    <h6>
        <i class="bi bi-lightning-charge text-warning"></i> Menu Cepat
    </h6>
    <div class="row g-2">
        @foreach ([
            ['route' => 'siswa.informasi-ekskul.index', 'icon' => 'info-circle',     'label' => 'Informasi Ekskul', 'color' => 'text-info'],
            ['route' => 'siswa.tes.index',              'icon' => 'patch-question',  'label' => 'Tes Rekomendasi',  'color' => 'text-warning'],
            ['route' => 'siswa.pendaftaran.index',      'icon' => 'pencil-square',   'label' => 'Pendaftaran',      'color' => 'text-primary'],
            ['route' => 'siswa.pengumuman.index',       'icon' => 'megaphone',       'label' => 'Pengumuman',       'color' => 'text-success'],
        ] as $menu)
            <div class="col-6 col-md-3">
                <a href="{{ route($menu['route']) }}" class="shortcut-item">
                    <i class="bi bi-{{ $menu['icon'] }} {{ $menu['color'] }}"></i>
                    <span>{{ $menu['label'] }}</span>
                </a>
            </div>
        @endforeach
    </div>
</div>

@endsection
