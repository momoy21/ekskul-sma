<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Ekskul SMA Global Indonesia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

{{-- ═══════════════ SIDEBAR ══ --}}
<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill text-white d-block mb-1" style="font-size:1.3rem;opacity:.9"></i>
        <h6>Sistem Ekstrakurikuler</h6>
        <small>SMA Global Indonesia</small>
    </div>

    <nav class="py-2">
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i> Dashboard
        </a>

        <div class="sidebar-section-label">Master Data</div>

        <a href="{{ route('admin.akun-siswa.index') }}"
           class="nav-link {{ request()->routeIs('admin.akun-siswa.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Akun Siswa
        </a>
        <a href="{{ route('admin.kelas.index') }}"
           class="nav-link {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}">
            <i class="bi bi-door-open"></i> Kelas
        </a>
        <a href="{{ route('admin.pembina.index') }}"
           class="nav-link {{ request()->routeIs('admin.pembina.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Pembina
        </a>
        <a href="{{ route('admin.kategori-ekskul.index') }}"
           class="nav-link {{ request()->routeIs('admin.kategori-ekskul.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Kategori Ekskul
        </a>
        <a href="{{ route('admin.tahun-ajaran.index') }}"
           class="nav-link {{ request()->routeIs('admin.tahun-ajaran.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Tahun Ajaran
        </a>
        <a href="{{ route('admin.kriteria.index') }}"
           class="nav-link {{ request()->routeIs('admin.kriteria.*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> Kriteria SAW
        </a>
        <a href="{{ route('admin.ekskul.index') }}"
           class="nav-link {{ request()->routeIs('admin.ekskul.*') ? 'active' : '' }}">
            <i class="bi bi-trophy"></i> Informasi Ekskul
        </a>
        <a href="{{ route('admin.soal.index') }}"
           class="nav-link {{ request()->routeIs('admin.soal.*') ? 'active' : '' }}">
            <i class="bi bi-patch-question"></i> Soal Rekomendasi
        </a>

        <div class="sidebar-section-label">Pendaftaran</div>

        <a href="{{ route('admin.pendaftaran.index') }}"
           class="nav-link {{ request()->routeIs('admin.pendaftaran.index') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Timeline & Seleksi
        </a>

        <a href="{{ route('admin.pendaftaran.hasil') }}"
           class="nav-link {{ request()->routeIs('admin.pendaftaran.hasil') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Hasil Pendaftaran
        </a>

        <div class="sidebar-section-label">Laporan</div>

        <a href="{{ route('admin.laporan-absensi.index') }}"
           class="nav-link {{ request()->routeIs('admin.laporan-absensi.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-pdf"></i> Absensi (PDF)
        </a>
        <a href="{{ route('admin.laporan-final.index') }}"
           class="nav-link {{ request()->routeIs('admin.laporan-final.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-spreadsheet"></i> Laporan Final
        </a>
    </nav>

    {{-- User info + logout --}}
    <div style="margin-top:auto;padding:1rem 0.75rem 1rem;border-top:1px solid rgba(255,255,255,.08)">
        <div class="d-flex align-items-center gap-2 mb-2 px-2">
            <div style="width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.12);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-person-fill" style="font-size:.8rem;color:#A8C4E0"></i>
            </div>
            <div style="min-width:0">
                <div style="font-size:.8rem;font-weight:600;color:#fff;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ session('username') }}
                </div>
                <div style="font-size:.68rem;color:#6B93BA">Administrator</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100"
                    style="background:rgba(255,255,255,.07);color:#90AFC8;
                           font-size:.775rem;border:1px solid rgba(255,255,255,.1);
                           border-radius:8px;padding:.35rem .75rem">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    </div>

</aside>

{{-- ══════════════════════ MAIN CONTENT ══ --}}
<div class="main-content" id="mainContent">

    {{-- Navbar atas --}}
    <nav class="top-navbar">
        <button class="navbar-toggler me-3" id="sidebarToggle" type="button">
            <i class="bi bi-list" style="font-size:1.25rem"></i>
        </button>

        <span class="page-title" style="font-size:1rem;font-weight:700">
            @yield('title', 'Dashboard')
        </span>

        <div class="ms-auto d-flex align-items-center gap-2">
            @php $ta = \App\Models\TahunAjaran::aktif()->first(); @endphp
            @if ($ta)
                <span class="badge bg-primary-subtle text-primary px-3 py-2" style="font-size:.75rem">
                    <i class="bi bi-calendar3 me-1"></i>
                    {{ $ta->label }}
                </span>
            @endif

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-md-inline" style="font-size:.825rem">{{ session('username') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-color:var(--border-color)">
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalGantiPassword">
                            <i class="bi bi-key me-2"></i> Ganti Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @include('components.alert')

    <div class="container-fluid py-4 px-4">
        @yield('content')
    </div>

</div>

{{-- Modal Ganti Password --}}
<div class="modal fade" id="modalGantiPassword" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:var(--radius-lg);border-color:var(--border-color)">
            <div class="modal-header" style="border-bottom-color:var(--border-color)">
                <h6 class="modal-title fw-bold">Ganti Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input type="password" id="newPasswordAdmin" name="password_baru"
                               class="form-control" placeholder="Min. 8 karakter" required minlength="8">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="document.getElementById('newPasswordAdmin').type =
                                         document.getElementById('newPasswordAdmin').type === 'password' ? 'text' : 'password'">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password_baru')
                        <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer" style="border-top-color:var(--border-color)">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
