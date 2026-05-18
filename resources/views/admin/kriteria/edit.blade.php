@extends('layouts.app')
@section('title', 'Edit Kriteria')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.kriteria.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Edit Kriteria {{ $kriteria->kode }}</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.kriteria.update', $kriteria) }}">
        @csrf @method('PUT')

        {{-- Kode- readonly --}}
        <div class="mb-3">
            <label class="form-label">Kode Kriteria</label>
            <input type="text" class="form-control bg-light font-monospace fw-bold"
                   value="{{ $kriteria->kode }}" readonly>
            <div class="form-text">Kode tidak dapat diubah.</div>
        </div>

        {{-- Sifat- readonly --}}
        <div class="mb-3">
            <label class="form-label">Sifat Atribut</label>
            <input type="text" class="form-control bg-light"
                   value="{{ ucfirst($kriteria->tipe_atribut) }}" readonly>
            <div class="form-text text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Sifat tidak dapat diubah- berpengaruh langsung ke logika normalisasi SAW.
            </div>
        </div>

        {{-- Nama Kriteria --}}
        <div class="mb-3">
            <label class="form-label" for="nama_kriteria">
                Nama Kriteria <span class="text-danger">*</span>
            </label>
            <input type="text" id="nama_kriteria" name="nama_kriteria"
                   class="form-control @error('nama_kriteria') is-invalid @enderror"
                   value="{{ old('nama_kriteria', $kriteria->nama_kriteria) }}" maxlength="80">
            @error('nama_kriteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Deskripsi untuk Siswa --}}
        <div class="mb-3">
            <label class="form-label" for="deskripsi_siswa">Deskripsi untuk Siswa</label>
            <textarea id="deskripsi_siswa" name="deskripsi_siswa" rows="3"
                      class="form-control @error('deskripsi_siswa') is-invalid @enderror"
                      placeholder="Kalimat penjelasan yang ditampilkan kepada siswa saat mengisi bobot...">{{ old('deskripsi_siswa', $kriteria->deskripsi_siswa) }}</textarea>
            @error('deskripsi_siswa')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">
                Ditampilkan di bawah setiap kriteria saat siswa mengisi bobot di Tahap 1 tes.
            </div>
        </div>

        {{-- Status --}}
        <div class="mb-4">
            <label class="form-label">Status</label>
            <div class="d-flex gap-4">
                @foreach ([1 => 'Aktif', 0 => 'Nonaktif'] as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active"
                               id="st{{ $val }}" value="{{ $val }}"
                               {{ old('is_active', $kriteria->is_active ? 1 : 0) == $val ? 'checked' : '' }}>
                        <label class="form-check-label" for="st{{ $val }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <div class="form-text">
                Nonaktif = kriteria tidak masuk perhitungan SAW dan tidak tampil di tes siswa.
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.kriteria.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
