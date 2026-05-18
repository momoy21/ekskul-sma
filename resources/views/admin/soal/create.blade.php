@extends('layouts.app')
@section('title', 'Tambah Soal')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.soal.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-1">Tambah Soal Rekomendasi</h6>
    <p class="text-muted mb-4" style="font-size:.83rem">
        Setiap soal harus dikaitkan ke minimal satu kriteria dan satu ekskul yang relevan.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-4" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.soal.store') }}">
        @csrf

        {{-- Kode soal- auto generate, tidak bisa diedit --}}
        <div class="mb-3">
            <label class="form-label">Kode Soal</label>
            <input type="text" class="form-control bg-light font-monospace fw-bold"
                   value="{{ $kodeBaru }}" readonly>
            <div class="form-text">Di-generate otomatis secara berurutan.</div>
        </div>

        {{-- Kriteria --}}
        <div class="mb-3">
            <label class="form-label">Kriteria <span class="text-danger">*</span></label>
            <select name="kriteria_id" class="form-select @error('kriteria_id') is-invalid @enderror">
                <option value="">-- Pilih Kriteria --</option>
                @foreach ($kriteriaList as $k)
                    <option value="{{ $k->kriteria_id }}"
                        {{ old('kriteria_id') == $k->kriteria_id ? 'selected' : '' }}>
                        {{ $k->label }}
                    </option>
                @endforeach
            </select>
            @error('kriteria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Teks Soal --}}
        <div class="mb-3">
            <label class="form-label">Teks Soal <span class="text-danger">*</span></label>
            <textarea name="teks_soal" rows="3"
                      class="form-control @error('teks_soal') is-invalid @enderror"
                      placeholder="Tulis pertanyaan untuk siswa di sini...">{{ old('teks_soal') }}</textarea>
            @error('teks_soal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Siswa menjawab dengan skala 1 (Sangat Tidak Setuju) – 5 (Sangat Setuju).</div>
        </div>

        {{-- Ekskul Terkait (checkbox list) --}}
        <div class="mb-4">
            <label class="form-label">
                Ekstrakurikuler Terkait <span class="text-danger">*</span>
            </label>
            <p class="text-muted mb-2" style="font-size:.8rem">
                Pilih ekskul yang relevan. Jawaban siswa pada soal ini akan masuk ke perhitungan nilai ekskul terpilih.
            </p>

            {{-- Tombol pilih/batal semua --}}
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
                    <div class="px-2 py-1" style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em">
                       - {{ $hari }}-
                    </div>
                    @foreach ($list as $e)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="ekskul_ids[]" value="{{ $e->ekskul_id }}"
                                   id="ekskul_{{ $e->ekskul_id }}"
                                   {{ in_array($e->ekskul_id, old('ekskul_ids', [])) ? 'checked' : '' }}>
                            <label class="form-check-label d-flex align-items-center gap-2" for="ekskul_{{ $e->ekskul_id }}">
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

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan
            </button>
            <a href="{{ route('admin.soal.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
