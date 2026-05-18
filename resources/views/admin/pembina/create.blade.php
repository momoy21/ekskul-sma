@extends('layouts.app')
@section('title', 'Tambah Pembina')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.pembina.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Tambah Pembina</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pembina.store') }}">
        @csrf

        <div class="mb-4">
            <label class="form-label" for="nama_lengkap">Nama Pembina <span class="text-danger">*</span></label>
            <input type="text" id="nama_lengkap" name="nama_lengkap"
                   class="form-control @error('nama_lengkap') is-invalid @enderror"
                   placeholder="Contoh: Mr Pram, Ms Rini"
                   value="{{ old('nama_lengkap') }}" maxlength="100">
            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan
            </button>
            <a href="{{ route('admin.pembina.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
