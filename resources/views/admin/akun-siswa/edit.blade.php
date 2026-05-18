@extends('layouts.app')
@section('title', 'Edit Akun Siswa')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.akun-siswa.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Edit Akun Siswa</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-4" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.akun-siswa.update', $siswa) }}">
        @csrf @method('PUT')

        {{-- NISN tidak bisa diubah --}}
        <div class="mb-3">
            <label class="form-label">NISN</label>
            <input type="text" class="form-control font-monospace bg-light"
                   value="{{ $siswa->nisn }}" readonly>
            <div class="form-text">NISN tidak dapat diubah.</div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" id="nama_lengkap" name="nama_lengkap"
                   class="form-control @error('nama_lengkap') is-invalid @enderror"
                   value="{{ old('nama_lengkap', $siswa->nama_lengkap) }}" maxlength="100">
            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="tanggal_lahir">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                   class="form-control @error('tanggal_lahir') is-invalid @enderror"
                   value="{{ old('tanggal_lahir', $siswa->tanggal_lahir->format('Y-m-d')) }}">
            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
            <div class="d-flex gap-4">
                @foreach (['L' => 'Laki-laki', 'P' => 'Perempuan'] as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jenis_kelamin"
                               id="jk{{ $val }}" value="{{ $val }}"
                               {{ old('jenis_kelamin', $siswa->jenis_kelamin) === $val ? 'checked' : '' }}>
                        <label class="form-check-label" for="jk{{ $val }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="kelas_id">Kelas <span class="text-danger">*</span></label>
            <select id="kelas_id" name="kelas_id"
                    class="form-select @error('kelas_id') is-invalid @enderror">
                @foreach ($kelasList->groupBy('tingkat') as $tingkat => $kList)
                    <optgroup label="Tingkat {{ $tingkat }}">
                        @foreach ($kList as $k)
                            <option value="{{ $k->kelas_id }}"
                                {{ old('kelas_id', $siswa->kelas_id) == $k->kelas_id ? 'selected' : '' }}>
                                {{ $k->label }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="d-flex gap-4">
                @foreach (['aktif' => 'Aktif', 'alumni' => 'Alumni'] as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status"
                               id="status{{ ucfirst($val) }}" value="{{ $val }}"
                               {{ old('status', $siswa->status) === $val ? 'checked' : '' }}>
                        <label class="form-check-label" for="status{{ ucfirst($val) }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            @error('status')<div class="invalid-feedback" style="display:block">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_baru">Password Baru</label>
            <div class="input-group">
                <input type="password" id="password_baru" name="password_baru"
                       class="form-control @error('password_baru') is-invalid @enderror"
                       placeholder="Kosongkan jika tidak ingin mengubah">
                <button type="button" class="btn btn-outline-secondary"
                        onclick="const i=document.getElementById('password_baru');i.type=i.type==='password'?'text':'password'">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password_baru')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.akun-siswa.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
