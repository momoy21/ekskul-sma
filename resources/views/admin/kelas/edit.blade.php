@extends('layouts.app')
@section('title', 'Edit Kelas')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.kelas.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Edit Kelas {{ $kelas->tingkat }} {{ $kelas->nama_kelas }}</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-4" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.kelas.update', $kelas) }}">
        @csrf @method('PUT')

        {{-- Tingkat --}}
        <div class="mb-4">
            <label class="form-label">Tingkat <span class="text-danger">*</span></label>
            <div class="d-flex gap-4">
                @foreach ([10, 11, 12] as $t)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tingkat"
                               id="tingkat{{ $t }}" value="{{ $t }}"
                               {{ old('tingkat', $kelas->tingkat) == $t ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="tingkat{{ $t }}">{{ $t }}</label>
                    </div>
                @endforeach
            </div>
            <div class="form-text text-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Tidak disarankan diubah jika sudah ada siswa di kelas ini.
            </div>
        </div>

        {{-- Nama Kelas --}}
        <div class="mb-4">
            <label class="form-label" for="nama_kelas">
                Nama Kelas <span class="text-danger">*</span>
            </label>
            <input type="text" id="nama_kelas" name="nama_kelas"
                   class="form-control @error('nama_kelas') is-invalid @enderror"
                   value="{{ old('nama_kelas', $kelas->nama_kelas) }}"
                   maxlength="20">
            @error('nama_kelas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Status --}}
        <div class="mb-4">
            <label class="form-label">Status</label>
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_active"
                           id="aktif" value="1"
                           {{ old('is_active', $kelas->is_active) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="aktif">Aktif</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_active"
                           id="nonaktif" value="0"
                           {{ old('is_active', $kelas->is_active) == 0 ? 'checked' : '' }}>
                    <label class="form-check-label" for="nonaktif">Nonaktif</label>
                </div>
            </div>
            <div class="form-text">Nonaktif = tidak muncul di dropdown pemilihan kelas.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary px-4">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
