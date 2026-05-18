@extends('layouts.app')
@section('title', 'Laporan Absensi')

@section('content')

<div class="form-card mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <form method="GET" action="{{ route('admin.laporan-absensi.index') }}">
                <div class="col-md-12">
                    <label class="form-label">Tahun Ajaran & Semester</label>
                    <select name="tahun_ajaran_id" class="form-select" onchange="this.form.submit()">
                        @foreach ($tahunAjaranList as $ta)
                            <option value="{{ $ta->tahun_ajaran_id }}"
                                {{ $tahunAjaranId == $ta->tahun_ajaran_id ? 'selected' : '' }}>
                                {{ $ta->label }} {{ $ta->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        @if ($periode)
            <div class="col-md-9 d-flex gap-2">
                @if ($ekskulList->isEmpty())
                    <form method="POST" action="{{ route('admin.laporan-absensi.finalisasi') }}"
                          class="d-inline"
                          data-confirm="Proses ini akan menyimpan pilihan final siswa ke tabel peserta ekskul dan membuat data absensi, lanjutkan?"
                          data-confirm-title="Generate Data Absensi?"
                          data-confirm-type="info"
                          data-confirm-btn="Generate">
                        @csrf
                        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaranId }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> Generate Absensi
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

@if ($periode && ! $periode->tahunAjaran->is_active)
    <div class="alert alert-secondary d-flex align-items-center gap-2 mb-4" style="font-size:.85rem">
        <i class="bi bi-lock-fill flex-shrink-0"></i>
        <strong>{{ $periode->tahunAjaran->label }}</strong> telah terkunci.
        Data tidak dapat diubah, hanya bisa diunduh ulang.
    </div>
@endif

<div class="table-card">
    <div class="card-header">
        <h6 class="fw-bold mb-0">
            Daftar Ekskul
            @if ($periode)
               - {{ $periode->tahunAjaran->label }}
            @endif
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">No</th>
                    <th>Nama Ekskul</th>
                    <th>Pembina</th>
                    <th class="text-center" style="width:120px">Jumlah Siswa</th>
                    <th class="text-center" style="width:200px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ekskulList as $no => $item)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td class="fw-semibold">{{ $item->nama_ekskul }}</td>
                        <td style="font-size:.85rem">{{ $item->nama_pembina }}</td>
                        <td class="text-center">
                            <span class="fw-semibold">{{ $item->jumlah_siswa }}</span>
                            <span class="text-muted" style="font-size:.78rem"> siswa</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('admin.laporan-absensi.preview', ['ekskul_id' => $item->ekskul_id, 'periode_id' => $periode->periode_id]) }}"
                                   target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Preview
                                </a>
                                <a href="{{ route('admin.laporan-absensi.download', ['ekskul_id' => $item->ekskul_id, 'periode_id' => $periode->periode_id]) }}"
                                   class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark-x d-block mb-2" style="font-size:2rem"></i>
                            {{ $periode ? 'Belum ada peserta ekskul di periode ini.' : 'Pilih tahun ajaran dan semester.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
