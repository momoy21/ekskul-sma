@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Greeting --}}
<div class="mb-4">
    <h5 class="fw-bold mb-0">Halo, Admin 👋</h5>
    <p class="text-muted mb-0" style="font-size:.875rem">
        Selamat datang di Sistem Ekstrakurikuler SMA Global Indonesia
    </p>
</div>

{{-- Card statistik --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="bg-white rounded-3 p-4 shadow-sm d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;flex-shrink:0">
                <i class="bi bi-people-fill text-primary" style="font-size:1.4rem"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size:1.6rem;line-height:1">{{ $stats['total_siswa_aktif'] }}</div>
                <div class="text-muted" style="font-size:.8rem">Siswa Aktif</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="bg-white rounded-3 p-4 shadow-sm d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;flex-shrink:0">
                <i class="bi bi-trophy-fill text-success" style="font-size:1.4rem"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size:1.6rem;line-height:1">{{ $stats['total_ekskul_aktif'] }}</div>
                <div class="text-muted" style="font-size:.8rem">Ekskul Aktif</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="bg-white rounded-3 p-4 shadow-sm d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;flex-shrink:0">
                <i class="bi bi-pencil-square text-warning" style="font-size:1.4rem"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size:1.6rem;line-height:1">{{ $stats['total_pendaftar'] }}</div>
                <div class="text-muted" style="font-size:.8rem">Pendaftar Semester Ini</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="bg-white rounded-3 p-4 shadow-sm d-flex align-items-center gap-3">
            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;flex-shrink:0">
                <i class="bi bi-calendar3 text-info" style="font-size:1.4rem"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size:1rem;line-height:1.3">
                    {{ $tahunAktif ? $tahunAktif->label : '—' }}
                </div>
                <div class="text-muted" style="font-size:.8rem">
                    {{ $tahunAktif ? 'Aktif' : 'Belum ada tahun ajaran' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Status periode pendaftaran --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="bg-white rounded-3 p-4 shadow-sm h-100">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-calendar-check text-primary me-2"></i>
                Status Pendaftaran Semester Ini
            </h6>

            @if ($periodeAktif)
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">Tahun Ajaran</span>
                        <span class="fw-semibold" style="font-size:.875rem">{{ $tahunAktif->label }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">Semester</span>
                        <span class="fw-semibold" style="font-size:.875rem">{{ ucfirst($periodeAktif->semester) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">Tanggal Buka</span>
                        <span class="fw-semibold" style="font-size:.875rem">{{ $periodeAktif->tanggal_buka->format('d/m/Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted" style="font-size:.85rem">Tanggal Tutup</span>
                        <span class="fw-semibold" style="font-size:.875rem">{{ $periodeAktif->tanggal_tutup->format('d/m/Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted" style="font-size:.85rem">Status</span>
                        @if ($periodeAktif->pendaftaran_sedang_buka)
                            <span class="badge bg-success">Sedang Buka</span>
                        @elseif ($periodeAktif->pemilihan_ulang_aktif)
                            <span class="badge bg-warning text-dark">Masa Pemilihan Ulang</span>
                        @elseif ($periodeAktif->pengumuman_tersedia)
                            <span class="badge bg-info">Pengumuman</span>
                        @else
                            <span class="badge bg-secondary">Belum Dibuka</span>
                        @endif
                    </div>
                </div>

                <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-sm btn-outline-primary mt-3 w-100">
                    <i class="bi bi-gear me-1"></i> Kelola Timeline Pendaftaran
                </a>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x text-muted" style="font-size:2.5rem"></i>
                    <p class="text-muted mt-2 mb-3" style="font-size:.875rem">
                        Timeline pendaftaran belum diatur untuk semester ini.
                    </p>
                    <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus me-1"></i> Set Timeline Pendaftaran
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Akses cepat menu --}}
    <div class="col-lg-6">
        <div class="bg-white rounded-3 p-4 shadow-sm h-100">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-lightning-charge text-warning me-2"></i>
                Akses Cepat
            </h6>
            <div class="row g-2">
                @foreach ([
                    ['route' => 'admin.akun-siswa.create',    'icon' => 'person-plus',        'label' => 'Tambah Siswa',      'color' => 'primary'],
                    ['route' => 'admin.ekskul.create',        'icon' => 'trophy',             'label' => 'Tambah Ekskul',     'color' => 'success'],
                    ['route' => 'admin.soal.create',          'icon' => 'patch-plus',         'label' => 'Tambah Soal',       'color' => 'info'],
                    ['route' => 'admin.laporan-absensi.index','icon' => 'file-earmark-pdf',   'label' => 'Laporan Absensi',   'color' => 'danger'],
                    ['route' => 'admin.laporan-final.index',  'icon' => 'file-earmark-excel', 'label' => 'Laporan Final',     'color' => 'warning'],
                    ['route' => 'admin.pendaftaran.index',    'icon' => 'calendar-check',     'label' => 'Timeline Daftar',   'color' => 'secondary'],
                ] as $menu)
                    <div class="col-6">
                        <a href="{{ route($menu['route']) }}"
                           class="d-flex align-items-center gap-2 p-3 rounded-3 text-decoration-none border"
                           style="transition:.15s;font-size:.83rem"
                           onmouseover="this.style.background='#f8fafc'"
                           onmouseout="this.style.background=''"
                        >
                            <i class="bi bi-{{ $menu['icon'] }} text-{{ $menu['color'] }}" style="font-size:1.1rem;flex-shrink:0"></i>
                            <span class="fw-semibold text-dark">{{ $menu['label'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
