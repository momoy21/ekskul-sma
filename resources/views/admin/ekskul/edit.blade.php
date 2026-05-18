@extends('layouts.app')
@section('title', 'Edit Ekskul')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.ekskul.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-short" style="font-size:1.1rem"></i> Kembali
    </a>
</div>

<div class="form-card">
    <h6 class="fw-bold mb-4">Edit Ekstrakurikuler - {{ $ekskul->nama_ekskul }}</h6>

    @if ($errors->any())
        <div class="alert alert-danger py-2 mb-4" style="font-size:.85rem">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.ekskul.update', $ekskul) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                <input type="text" name="nama_ekskul"
                       class="form-control @error('nama_ekskul') is-invalid @enderror"
                       value="{{ old('nama_ekskul', $ekskul->nama_ekskul) }}" maxlength="100">
                @error('nama_ekskul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                <select name="kategori_ekskul_id" class="form-select @error('kategori_ekskul_id') is-invalid @enderror">
                    @foreach ($kategoriList as $k)
                        <option value="{{ $k->kategori_ekskul_id }}"
                            {{ old('kategori_ekskul_id', $ekskul->kategori_ekskul_id) == $k->kategori_ekskul_id ? 'selected' : '' }}>
                            {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                @error('kategori_ekskul_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Hari Pelaksanaan <span class="text-danger">*</span></label>
                <select name="hari_pelaksanaan" class="form-select @error('hari_pelaksanaan') is-invalid @enderror">
                    @foreach (['Senin', 'Selasa', 'Kamis', 'Jumat'] as $hari)
                        <option value="{{ $hari }}"
                            {{ old('hari_pelaksanaan', $ekskul->hari_pelaksanaan) === $hari ? 'selected' : '' }}>
                            {{ $hari }}
                        </option>
                    @endforeach
                </select>
                @error('hari_pelaksanaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                <input type="text" name="lokasi"
                       class="form-control @error('lokasi') is-invalid @enderror"
                       value="{{ old('lokasi', $ekskul->lokasi) }}" maxlength="100">
                @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Biaya Tambahan <span class="text-danger">*</span></label>
                <select name="biaya_tambahan" class="form-select @error('biaya_tambahan') is-invalid @enderror">
                    <option value="">-- Pilih Kategori Biaya --</option>
                    <option value="1" {{ old('biaya_tambahan', $ekskul->biaya_tambahan) == 1 ? 'selected' : '' }}>Tidak Ada Biaya (Rp 0)</option>
                    <option value="2" {{ old('biaya_tambahan', $ekskul->biaya_tambahan) == 2 ? 'selected' : '' }}>Sedikit Biaya (Rp 1.000 - Rp 100.000)</option>
                    <option value="3" {{ old('biaya_tambahan', $ekskul->biaya_tambahan) == 3 ? 'selected' : '' }}>Terjangkau (Rp 101.000 - Rp 200.000)</option>
                    <option value="4" {{ old('biaya_tambahan', $ekskul->biaya_tambahan) == 4 ? 'selected' : '' }}>Sedikit Mahal (Rp 201.000 - Rp 300.000)</option>
                    <option value="5" {{ old('biaya_tambahan', $ekskul->biaya_tambahan) == 5 ? 'selected' : '' }}>Mahal (Rp 301.000+)</option>
                </select>
                @error('biaya_tambahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Fasilitas <span class="text-danger">*</span></label>
                <select name="fasilitas_level" class="form-select @error('fasilitas_level') is-invalid @enderror">
                    <option value="">-- Pilih Level Fasilitas --</option>
                    <option value="1" {{ old('fasilitas_level', $ekskul->fasilitas_level) == 1 ? 'selected' : '' }}>Seluruhnya dibawa sendiri</option>
                    <option value="2" {{ old('fasilitas_level', $ekskul->fasilitas_level) == 2 ? 'selected' : '' }}>Beberapa dari sekolah, lebih banyak dibawa sendiri</option>
                    <option value="3" {{ old('fasilitas_level', $ekskul->fasilitas_level) == 3 ? 'selected' : '' }}>Sebagian disediakan sekolah, sebagian sendiri</option>
                    <option value="4" {{ old('fasilitas_level', $ekskul->fasilitas_level) == 4 ? 'selected' : '' }}>Beberapa dibawa sendiri, lebih banyak disediakan sekolah</option>
                    <option value="5" {{ old('fasilitas_level', $ekskul->fasilitas_level) == 5 ? 'selected' : '' }}>Dari semua disediakan sekolah</option>
                </select>
                @error('fasilitas_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Intensitas Kegiatan <span class="text-danger">*</span></label>
                <select name="intensitas_kegiatan" class="form-select @error('intensitas_kegiatan') is-invalid @enderror">
                    <option value="">-- Pilih Intensitas --</option>
                    <option value="1" {{ old('intensitas_kegiatan', $ekskul->intensitas_kegiatan) == 1 ? 'selected' : '' }}>Intensitas Sangat Tinggi</option>
                    <option value="2" {{ old('intensitas_kegiatan', $ekskul->intensitas_kegiatan) == 2 ? 'selected' : '' }}>Intensitas Tinggi</option>
                    <option value="3" {{ old('intensitas_kegiatan', $ekskul->intensitas_kegiatan) == 3 ? 'selected' : '' }}>Intensitas Sedang</option>
                    <option value="4" {{ old('intensitas_kegiatan', $ekskul->intensitas_kegiatan) == 4 ? 'selected' : '' }}>Intensitas Rendah</option>
                    <option value="5" {{ old('intensitas_kegiatan', $ekskul->intensitas_kegiatan) == 5 ? 'selected' : '' }}>Intensitas Sangat Rendah</option>
                </select>
                @error('intensitas_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Foto Kegiatan</label>
                @if ($ekskul->foto_path)
                    <div class="mb-2">
                        <img src="{{ $ekskul->foto_url }}" alt="Foto saat ini"
                             class="rounded" style="max-height:100px;max-width:100%;object-fit:cover">
                        <div class="form-text">Foto saat ini. Upload baru untuk mengganti.</div>
                    </div>
                @endif
                <input type="file" id="foto-input" name="foto" accept="image/jpg,image/jpeg,image/png"
                       class="form-control @error('foto') is-invalid @enderror">
                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <img id="foto-preview" src="" alt="Preview" class="mt-2 rounded d-none"
                     style="max-height:100px;max-width:100%;object-fit:cover">
            </div>

            <div class="col-12">
                <label class="form-label">Deskripsi Kegiatan</label>
                <textarea name="deskripsi_kegiatan" rows="4" class="form-control">{{ old('deskripsi_kegiatan', $ekskul->deskripsi_kegiatan) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Pembina <span class="text-danger">*</span></label>
                <div class="checkbox-list">
                    @foreach ($pembinaList as $p)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="pembina_ids[]" value="{{ $p->pembina_id }}"
                                   id="pembina_{{ $p->pembina_id }}"
                                   {{ in_array($p->pembina_id, old('pembina_ids', $pembinaSelected)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pembina_{{ $p->pembina_id }}">
                                {{ $p->nama_lengkap }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('pembina_ids')
                    <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label">Status</label>
                <div class="d-flex gap-4">
                    @foreach ([1 => 'Aktif', 0 => 'Nonaktif'] as $val => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_active"
                                   id="status{{ $val }}" value="{{ $val }}"
                                   {{ old('is_active', $ekskul->is_active ? 1 : 0) == $val ? 'checked' : '' }}>
                            <label class="form-check-label" for="status{{ $val }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">Nonaktif = tidak muncul di katalog siswa dan dropdown pendaftaran.</div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.ekskul.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
