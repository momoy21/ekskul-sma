<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Siswa') - Ekskul SMA Global Indonesia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        /* Navbar siswa */
        .siswa-navbar {
            background: #234478;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 2px 8px rgb(0 0 0/.2);
        }

        .siswa-navbar .nav-link {
            color: #cbd5e1;
            font-size: .85rem;
            padding: .85rem 1rem;
            display: flex;
            align-items: center;
            gap: .4rem;
            border-bottom: 3px solid transparent;
            transition: color .15s, border-color .15s;
            white-space: nowrap;
        }

        .siswa-navbar .nav-link:hover { color: #fff; }

        .siswa-navbar .nav-link.active {
            color: #fff;
            border-bottom-color: #fff;
            font-weight: 600;
        }

        .siswa-body { background: #f1f5f9; min-height: 100vh; }
    </style>

    @stack('styles')
</head>
<body class="siswa-body">

{{-- ═══════════════════════════════════════ NAVBAR SISWA ══ --}}
<nav class="siswa-navbar navbar navbar-expand-md">
    <div class="container-fluid px-4">

        {{-- Brand --}}
        <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2 me-4" href="{{ route('siswa.dashboard') }}">
            <i class="bi bi-mortarboard-fill text-white"></i>
            <span style="font-size:.85rem;line-height:1.2">Ekskul<br><small style="font-weight:400;color:#94a3b8;font-size:.7rem">SMA Global Indonesia</small></span>
        </a>

        {{-- Toggle untuk mobile --}}
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#siswaNav">
            <i class="bi bi-list text-white" style="font-size:1.3rem"></i>
        </button>

        {{-- Menu --}}
        <div class="collapse navbar-collapse" id="siswaNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}"
                       href="{{ route('siswa.dashboard') }}">
                        <i class="bi bi-grid"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.informasi-ekskul.*') ? 'active' : '' }}"
                       href="{{ route('siswa.informasi-ekskul.index') }}">
                        <i class="bi bi-info-circle"></i> Informasi Ekskul
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.tes.*') ? 'active' : '' }}"
                       href="{{ route('siswa.tes.index') }}">
                        <i class="bi bi-patch-question"></i> Tes Rekomendasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.pendaftaran.*') ? 'active' : '' }}"
                       href="{{ route('siswa.pendaftaran.index') }}">
                        <i class="bi bi-pencil-square"></i> Pendaftaran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.pengumuman.*') ? 'active' : '' }}"
                       href="{{ route('siswa.pengumuman.index') }}">
                        <i class="bi bi-megaphone"></i> Pengumuman
                    </a>
                </li>
            </ul>

            {{-- Profil siswa --}}
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px;height:28px;flex-shrink:0">
                            <i class="bi bi-person-fill text-dark" style="font-size:.75rem"></i>
                        </div>
                        <span>{{ session('nama') }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="px-3 py-2">
                                <div class="fw-semibold" style="font-size:.85rem">{{ session('nama') }}</div>
                                <div class="text-muted" style="font-size:.75rem">
                                    NISN: {{ session('nisn') }} · Kelas {{ session('label_kelas') }}
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalGantiPasswordSiswa">
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
                </li>
            </ul>
        </div>

    </div>
</nav>

{{-- Flash Toast --}}
@include('components.alert')

{{-- Konten halaman --}}
<div class="container-fluid py-4 px-4">
    @yield('content')
</div>

{{-- Modal Ganti Password Siswa --}}
<div class="modal fade" id="modalGantiPasswordSiswa" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Ganti Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('siswa.profile.password') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="fw-semibold mb-1" style="font-size:.85rem">{{ session('nama') }}</div>
                        <div class="text-muted" style="font-size:.78rem">Kelas {{ session('label_kelas') }}</div>
                    </div>
                    <label class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input type="password" id="newPasswordSiswa" name="password_baru"
                               class="form-control" placeholder="Min. 8 karakter" required minlength="8">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="document.getElementById('newPasswordSiswa').type = document.getElementById('newPasswordSiswa').type === 'password' ? 'text' : 'password'">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
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
