{{-- ════════════ kriteria/index.blade.php ════════════ --}}
@extends('layouts.app')
@section('title', 'Kriteria SAW')

@section('content')

<div class="alert alert-info d-flex gap-2 mb-4" style="font-size:.85rem">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        Kriteria bersifat tetap (C1–C5) dan tidak dapat ditambah atau dihapus.
        <strong>Kode</strong> dan <strong>Sifat (Benefit/Cost)</strong> tidak dapat diubah karena mempengaruhi logika perhitungan SAW.
        Yang dapat diedit: Nama Kriteria, Deskripsi untuk Siswa, dan Status Aktif.
    </div>
</div>

<div class="table-card">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari kriteria...',
            'filters' => [
                ['name' => 'status', 'placeholder' => 'Filter Status',
                 'options' => [1 => 'Aktif', 0 => 'Nonaktif']],
            ],
        ])
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:70px">Kode</th>
                    <th>Nama Kriteria</th>
                    <th style="width:100px">Sifat</th>
                    <th style="width:110px">Status</th>
                    <th style="width:80px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kriteria as $item)
                    <tr>
                        <td class="fw-bold font-monospace text-primary">{{ $item->kode }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->nama_kriteria }}</div>
                            @if ($item->deskripsi_siswa)
                                <div class="text-muted" style="font-size:.78rem">
                                    {{ Str::limit($item->deskripsi_siswa, 70) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $item->tipe_atribut === 'benefit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}"
                                  style="font-size:.75rem">
                                {{ ucfirst($item->tipe_atribut) }}
                            </span>
                        </td>
                        <td>
                            @include('components.status-toggle', [
                                'isActive'   => $item->is_active,
                                'route'      => 'admin.kriteria.toggle-status',
                                'routeParam' => $item,
                                'label'      => "Kriteria {$item->kode}",
                            ])
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.kriteria.edit', $item) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">Tidak ada data kriteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $kriteria])
    </div>
</div>
@endsection
