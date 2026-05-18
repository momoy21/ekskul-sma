{{-- ════════════ kategori-ekskul/index.blade.php ════════════ --}}
@extends('layouts.app')
@section('title', 'Kategori Ekskul')

@section('content')
<div class="table-card">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari kategori...',
            'createRoute'       => 'admin.kategori-ekskul.create',
            'createLabel'       => 'Tambah Kategori',
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
                    <th>Kategori Ekstrakurikuler</th>
                    <th style="width:120px">Status</th>
                    <th style="width:80px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kategori as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->nama_kategori }}</td>
                        <td>
                            @include('components.status-toggle', [
                                'isActive'   => $item->is_active,
                                'route'      => 'admin.kategori-ekskul.toggle-status',
                                'routeParam' => $item,
                                'label'      => $item->nama_kategori,
                            ])
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.kategori-ekskul.edit', $item) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2rem"></i>
                            Belum ada kategori ekskul.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $kategori])
    </div>
</div>
@endsection
