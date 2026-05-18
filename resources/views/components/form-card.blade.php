{{--
    components/form-card.blade.php
    Wrapper standar untuk halaman create dan edit.
    Berisi: tombol kembali di kiri atas + card form.

    Props:
    - $backRoute  : route tombol kembali
    - $backLabel  : label tombol kembali (default: "Kembali")
    - $title      : judul card (opsional)

    Contoh di halaman create:
    @include('components.form-card', [
        'backRoute' => 'admin.kelas.index',
        'title'     => 'Tambah Kelas',
    ])
    ... isi form ...
    @endinclude ← TIDAK bisa, gunakan slot

    Cara yang benar- gunakan @component atau langsung di blade halaman:
    @include('components.form-card', ['backRoute' => 'admin.kelas.index'])
    lalu tambahkan form-card div di view langsung.
--}}

<div class="mb-3">
    <a href="{{ route($backRoute) }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i>
        {{ $backLabel ?? 'Kembali' }}
    </a>
</div>

<div class="form-card">
    @if (isset($title))
        <h6 class="fw-bold mb-4">{{ $title }}</h6>
    @endif

    {{-- Tampilkan semua error validasi jika ada --}}
    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-4">
            <div class="fw-semibold mb-1" style="font-size:.85rem">
                <i class="bi bi-exclamation-circle me-1"></i> Terdapat kesalahan:
            </div>
            <ul class="mb-0 ps-3" style="font-size:.83rem">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{ $slot ?? '' }}
</div>
