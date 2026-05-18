@extends('layouts.app')
@section('title', 'Tahun Ajaran')

@section('content')
<div class="table-card">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari tahun ajaran...',
            'createRoute'       => 'admin.tahun-ajaran.create',
            'createLabel'       => 'Buat Tahun Ajaran Baru',
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
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th style="width:100px">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tahunAjaran as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->label_tahun }}</td>
                        <td>{{ ucfirst($item->semester) }}</td>
                        <td>
                            <span class="{{ $item->is_active ? 'badge-aktif' : 'badge-nonaktif' }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem"></i>
                            Belum ada tahun ajaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $tahunAjaran])
    </div>
</div>
@endsection
