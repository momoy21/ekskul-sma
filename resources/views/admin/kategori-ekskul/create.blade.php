@extends('layouts.app')
@section('title', 'Tambah Kategori')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.kategori-ekskul.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Tambah Kategori Ekstrakurikuler</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.kategori-ekskul.store') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="nama_kategori">Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" id="nama_kategori" name="nama_kategori"
                   class="form-control @error('nama_kategori') is-invalid @enderror"
                   placeholder="Contoh: Seni dan Budaya, Olahraga"
                   value="{{ old('nama_kategori') }}" maxlength="80">
            @error('nama_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan
            </button>
            <a href="{{ route('admin.kategori-ekskul.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
