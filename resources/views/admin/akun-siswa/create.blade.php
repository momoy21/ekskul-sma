@extends('layouts.app')
@section('title', 'Tambah Akun Siswa')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.akun-siswa.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Tambah Akun Siswa</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-4" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.akun-siswa.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="nisn">NISN <span class="text-danger">*</span></label>
            <input type="text" id="nisn" name="nisn" inputmode="numeric" maxlength="10"
                   class="form-control font-monospace @error('nisn') is-invalid @enderror"
                   placeholder="10 digit angka" value="{{ old('nisn') }}">
            @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">NISN akan digunakan sebagai username login siswa.</div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" id="nama_lengkap" name="nama_lengkap"
                   class="form-control @error('nama_lengkap') is-invalid @enderror"
                   placeholder="Nama lengkap siswa" value="{{ old('nama_lengkap') }}" maxlength="100">
            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="tanggal_lahir">
                Tanggal Lahir <span class="text-danger">*</span>
            </label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                   class="form-control @error('tanggal_lahir') is-invalid @enderror"
                   value="{{ old('tanggal_lahir') }}">
            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">
                <i class="bi bi-info-circle me-1"></i>
                Akan digunakan sebagai password default (format DDMMYYYY).
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_kelamin"
                           id="laki" value="L" {{ old('jenis_kelamin') === 'L' ? 'checked' : '' }}>
                    <label class="form-check-label" for="laki">Laki-laki</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_kelamin"
                           id="perempuan" value="P" {{ old('jenis_kelamin') === 'P' ? 'checked' : '' }}>
                    <label class="form-check-label" for="perempuan">Perempuan</label>
                </div>
            </div>
            @error('jenis_kelamin')<div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label" for="kelas_id">Kelas <span class="text-danger">*</span></label>
            <select id="kelas_id" name="kelas_id"
                    class="form-select @error('kelas_id') is-invalid @enderror">
                <option value="">-- Pilih Kelas --</option>
                @foreach ($kelasList->groupBy('tingkat') as $tingkat => $kList)
                    <optgroup label="Tingkat {{ $tingkat }}">
                        @foreach ($kList as $k)
                            <option value="{{ $k->kelas_id }}"
                                {{ old('kelas_id') == $k->kelas_id ? 'selected' : '' }}>
                                {{ $k->label }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan
            </button>
            <a href="{{ route('admin.akun-siswa.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
