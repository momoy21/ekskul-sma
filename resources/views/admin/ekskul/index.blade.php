@extends('layouts.app')
@section('title', 'Master Data Ekskul')

@section('content')
<div class="table-card">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari nama ekskul...',
            'createRoute'       => 'admin.ekskul.create',
            'createLabel'       => 'Tambah Ekskul',
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
                    <th>Nama Ekskul</th>
                    <th>Pembina</th>
                    <th>Hari</th>
                    <th style="width:110px">Status</th>
                    <th style="width:80px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ekskul as $item)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $item->nama_ekskul }}</div>
                            <div class="text-muted" style="font-size:.78rem">{{ $item->kategori->nama_kategori }}</div>
                        </td>
                        <td style="font-size:.85rem">{{ $item->nama_pembina }}</td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:.78rem">
                                {{ $item->hari_pelaksanaan }}
                            </span>
                        </td>
                        <td>
                            @include('components.status-toggle', [
                                'isActive'   => $item->is_active,
                                'route'      => 'admin.ekskul.toggle-status',
                                'routeParam' => $item,
                                'label'      => $item->nama_ekskul,
                            ])
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.ekskul.edit', $item) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-trophy d-block mb-2" style="font-size:2rem"></i>
                            Belum ada data ekskul.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $ekskul])
    </div>
</div>
@endsection
