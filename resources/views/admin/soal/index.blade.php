@extends('layouts.app')
@section('title', 'Soal Rekomendasi')

@section('content')
<div class="table-card">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari teks soal...',
            'createRoute'       => 'admin.soal.create',
            'createLabel'       => 'Tambah Soal',
            'filters' => [
                ['name' => 'kriteria_id', 'placeholder' => 'Filter Kriteria',
                 'options' => $kriteriaList->mapWithKeys(fn($k) => [$k->kriteria_id => $k->label])->toArray()],
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
                    <th style="width:180px">Kriteria</th>
                    <th>Teks Soal</th>
                    <th style="width:110px">Status</th>
                    <th style="width:80px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($soal as $item)
                    <tr>
                        <td class="fw-bold font-monospace" style="font-size:.85rem">{{ $item->kode_soal }}</td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary" style="font-size:.75rem">
                                {{ $item->kriteria->kode }}- {{ $item->kriteria->nama_kriteria }}
                            </span>
                        </td>
                        <td style="font-size:.875rem">{{ Str::limit($item->teks_soal, 90) }}</td>
                        <td>
                            @include('components.status-toggle', [
                                'isActive'   => $item->is_active,
                                'route'      => 'admin.soal.toggle-status',
                                'routeParam' => $item,
                                'label'      => $item->kode_soal,
                            ])
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.soal.edit', $item) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-patch-question d-block mb-2" style="font-size:2rem"></i>
                            Belum ada soal rekomendasi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $soal])
    </div>
</div>
@endsection
