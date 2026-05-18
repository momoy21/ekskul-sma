{{-- ═══════════════════════ kelas/index.blade.php ═══════════════════════ --}}
@extends('layouts.app')
@section('title', 'Master Data Kelas')

@section('content')
<div class="table-card">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari nama kelas...',
            'createRoute'       => 'admin.kelas.create',
            'createLabel'       => 'Tambah Kelas',
            'filters' => [
                ['name' => 'tingkat', 'placeholder' => 'Filter Tingkat',
                 'options' => [10 => 'Tingkat 10', 11 => 'Tingkat 11', 12 => 'Tingkat 12']],
                ['name' => 'status',  'placeholder' => 'Filter Status',
                 'options' => [1 => 'Aktif', 0 => 'Nonaktif']],
            ],
        ])
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:80px">Tingkat</th>
                    <th>Nama Kelas</th>
                    <th style="width:120px">Status</th>
                    <th style="width:100px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kelas as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->tingkat }}</td>
                        <td>{{ $item->nama_kelas }}</td>
                        <td>
                            @include('components.status-toggle', [
                                'isActive'   => $item->is_active,
                                'route'      => 'admin.kelas.toggle-status',
                                'routeParam' => $item,
                                'label'      => "Kelas {$item->tingkat} {$item->nama_kelas}",
                            ])
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.kelas.edit', $item) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2rem"></i>
                            Tidak ada data kelas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $kelas])
    </div>
</div>
@endsection
