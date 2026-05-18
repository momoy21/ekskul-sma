@extends('layouts.app')
@section('title', 'Edit Soal')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.soal.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Edit Soal - {{ $soal->kode_soal }}</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-4" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.soal.update', $soal) }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Kode Soal</label>
            <input type="text" class="form-control bg-light font-monospace fw-bold"
                   value="{{ $soal->kode_soal }}" readonly>
            <div class="form-text">Kode soal tidak dapat diubah.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Kriteria <span class="text-danger">*</span></label>
            <select name="kriteria_id" class="form-select @error('kriteria_id') is-invalid @enderror">
                @foreach ($kriteriaList as $k)
                    <option value="{{ $k->kriteria_id }}"
                        {{ old('kriteria_id', $soal->kriteria_id) == $k->kriteria_id ? 'selected' : '' }}>
                        {{ $k->label }}
                    </option>
                @endforeach
            </select>
            @error('kriteria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Teks Soal <span class="text-danger">*</span></label>
            <textarea name="teks_soal" rows="3"
                      class="form-control @error('teks_soal') is-invalid @enderror">{{ old('teks_soal', $soal->teks_soal) }}</textarea>
            @error('teks_soal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Ekstrakurikuler Terkait <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="document.querySelectorAll('[name=\'ekskul_ids[]\']').forEach(c=>c.checked=true)">
                    Pilih Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="document.querySelectorAll('[name=\'ekskul_ids[]\']').forEach(c=>c.checked=false)">
                    Batal Semua
                </button>
            </div>
            <div class="checkbox-list">
                @foreach ($ekskulList->groupBy('hari_pelaksanaan') as $hari => $list)
                    <div class="px-2 py-1" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase">
                       - {{ $hari }}-
                    </div>
                    @foreach ($list as $e)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="ekskul_ids[]" value="{{ $e->ekskul_id }}"
                                   id="ek{{ $e->ekskul_id }}"
                                   {{ in_array($e->ekskul_id, old('ekskul_ids', $ekskulSelected)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ek{{ $e->ekskul_id }}">
                                {{ $e->nama_ekskul }}
                                <span class="text-muted" style="font-size:.75rem">{{ $e->kategori->nama_kategori }}</span>
                            </label>
                        </div>
                    @endforeach
                @endforeach
            </div>
            @error('ekskul_ids')
                <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Status</label>
            <div class="d-flex gap-4">
                @foreach ([1 => 'Aktif', 0 => 'Nonaktif'] as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active"
                               id="st{{ $val }}" value="{{ $val }}"
                               {{ old('is_active', $soal->is_active ? 1 : 0) == $val ? 'checked' : '' }}>
                        <label class="form-check-label" for="st{{ $val }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <div class="form-text">Nonaktif = tidak ditampilkan ke siswa pada tes berikutnya.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.soal.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
