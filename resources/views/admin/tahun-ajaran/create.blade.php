@extends('layouts.app')
@section('title', 'Tambah Tahun Ajaran')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-6">

@php
    $isTahunBaru = isset($saranSemester) && $saranSemester === 'ganjil';
@endphp

<div class="alert alert-warning d-flex gap-2 mb-4" style="font-size:.875rem">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>⚠️ Perhatian!</strong> Membuat tahun ajaran baru akan otomatis:
        <ul class="mb-0 mt-1 ps-3">
            <li>Menonaktifkan semester sebelumnya</li>
            <li>Mengunci data peserta ekskul semester lama</li>
            @if ($isTahunBaru)
                <li>Menaikkan tingkat kelas seluruh siswa aktif (10→11, 11→12)</li>
                <li>Siswa kelas 12 akan berubah status menjadi <strong>alumni</strong> dan tidak bisa login</li>
                <li>Siswa yang naik kelas ditempatkan sementara di kelas awal per tingkat</li>
            @endif
        </ul>
        <div class="mt-1 fw-semibold">Pastikan seluruh data semester berjalan sudah selesai sebelum melanjutkan.</div>
    </div>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Tambah Tahun Ajaran Baru</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}"
          data-confirm="Anda akan membuat {{ $saranMulai }}/{{ $saranSelesai }} semester {{ ucfirst($saranSemester) }}. Tindakan ini tidak dapat dibatalkan, lanjutkan?"
          data-confirm-title="Konfirmasi Buat Tahun Ajaran"
          data-confirm-type="warning"
          data-confirm-btn="Ya, Lanjutkan">
        @csrf

        <div class="mb-3">
            <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
            <div class="d-flex align-items-center gap-2">
                <input type="number" name="tahun_mulai"
                       class="form-control @error('tahun_mulai') is-invalid @enderror"
                       value="{{ old('tahun_mulai', $saranMulai) }}" min="2000" max="2100"
                       style="max-width:110px">
                <span class="fw-bold text-muted">/</span>
                <input type="number" name="tahun_selesai"
                       class="form-control @error('tahun_selesai') is-invalid @enderror"
                       value="{{ old('tahun_selesai', $saranSelesai) }}" min="2000" max="2100"
                       style="max-width:110px">
            </div>
            @error('tahun_mulai')<div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>@enderror
            @error('tahun_selesai')<div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Semester <span class="text-danger">*</span></label>
            <div class="d-flex gap-4">
                @foreach (['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="semester"
                               id="sem_{{ $val }}" value="{{ $val }}"
                               {{ old('semester', $saranSemester) === $val ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="sem_{{ $val }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            @error('semester')<div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-warning fw-semibold px-4">
            <i class="bi bi-plus-circle me-1"></i> Buat Tahun Ajaran Baru
        </button>
    </form>
</div>

</div>
</div>
@endsection
