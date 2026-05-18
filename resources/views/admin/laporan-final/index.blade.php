@extends('layouts.app')
@section('title', 'Laporan Final')

@section('content')

<div class="form-card mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <form method="GET" action="{{ route('admin.laporan-final.index') }}">
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
                    <form method="POST" action="{{ route('admin.laporan-final.finalisasi') }}"
                          class="d-inline"
                          data-confirm="Proses ini akan menyimpan pilihan final siswa ke tabel peserta ekskul dan membuat laporan Excel, lanjutkan?"
                          data-confirm-title="Generate Laporan Final?"
                          data-confirm-type="info"
                          data-confirm-btn="Generate">
                        @csrf
                        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaranId }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> Generate Laporan Final
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

@if ($dataKunci)
    <div class="alert alert-secondary d-flex align-items-center gap-2 mb-4" style="font-size:.85rem">
        <i class="bi bi-lock-fill flex-shrink-0"></i>
        Data tahun ajaran ini telah <strong>terkunci</strong>. Tidak dapat diubah, hanya bisa diunduh.
    </div>
@endif

<div class="table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0">Preview Spreadsheet</h6>
            @if ($periode)
                <p class="text-muted mb-0" style="font-size:.8rem">
                    {{ $periode->tahunAjaran->label }}
                </p>
            @endif
        </div>
        @if ($periode && $ekskulList->isNotEmpty())
            <a href="{{ route('admin.laporan-final.download', ['tahun_ajaran_id' => $tahunAjaranId]) }}"
               class="btn btn-success d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-excel"></i>
                Download Spreadsheet
            </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">No</th>
                    <th>Nama Ekskul</th>
                    <th>Pembina</th>
                    <th class="text-center" style="width:130px">Jumlah Siswa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ekskulList as $no => $item)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td class="fw-semibold">{{ $item->nama_ekskul }}</td>
                        <td style="font-size:.85rem">{{ $item->nama_pembina }}</td>
                        <td class="text-center fw-semibold">{{ $item->jumlah_siswa }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark-x d-block mb-2" style="font-size:2rem"></i>
                            {{ $periode ? 'Belum ada data peserta.' : 'Pilih tahun ajaran dan semester.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($ekskulList->isNotEmpty())
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="3" class="text-end">Total Siswa Terdaftar Ekskul:</td>
                        <td class="text-center">{{ $totalSiswa }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
