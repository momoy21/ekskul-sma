@extends('layouts.app')
@section('title', 'Akun Siswa')

@section('content')

{{-- Tab Aktif / Alumni --}}
<ul class="nav nav-tabs mb-0" style="border-bottom:none">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'aktif' ? 'active fw-semibold' : '' }}"
           href="{{ route('admin.akun-siswa.index', array_merge(request()->except('tab','page'), ['tab'=>'aktif'])) }}">
            <i class="bi bi-person-check me-1"></i> Kelas Aktif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'alumni' ? 'active fw-semibold' : '' }}"
           href="{{ route('admin.akun-siswa.index', array_merge(request()->except('tab','page'), ['tab'=>'alumni'])) }}">
            <i class="bi bi-person-dash me-1"></i> Alumni
        </a>
    </li>
</ul>

<div class="table-card" style="border-radius:0 12px 12px 12px">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari nama atau NISN...',
            'createRoute'       => 'admin.akun-siswa.create',
            'createLabel'       => 'Tambah Siswa',
            'filters' => [
                ['name' => 'tingkat', 'placeholder' => 'Filter Tingkat',
                 'options' => [10 => 'Tingkat 10', 11 => 'Tingkat 11', 12 => 'Tingkat 12']],
                ['name' => 'kelas_id', 'placeholder' => 'Filter Kelas',
                 'options' => $kelasList->mapWithKeys(fn($k) => [$k->kelas_id => $k->label])->toArray()],
            ],
        ])
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    {{-- Checkbox select-all (hanya tab aktif) --}}
                    @if ($tab === 'aktif')
                        <th style="width:40px">
                            <input type="checkbox" class="form-check-input" id="checkboxAll">
                        </th>
                    @endif
                    <th>NISN</th>
                    <th>Nama Lengkap</th>
                    <th>Kelas</th>
                    <th>Jenis Kelamin</th>
                    <th style="width:100px">Status</th>
                    <th style="width:80px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($siswa as $item)
                    <tr>
                        @if ($tab === 'aktif')
                            <td>
                                <input type="checkbox" class="form-check-input"
                                       data-row-checkbox value="{{ $item->siswa_id }}">
                            </td>
                        @endif
                        <td class="font-monospace" style="font-size:.82rem">{{ $item->nisn }}</td>
                        <td class="fw-semibold">{{ $item->nama_lengkap }}</td>
                        <td>{{ $item->label_kelas }}</td>
                        <td>{{ $item->label_jenis_kelamin }}</td>
                        <td>
                            <span class="{{ $item->pengguna->is_active ? 'badge-aktif' : 'badge-nonaktif' }}">
                                {{ $item->pengguna->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.akun-siswa.edit', $item) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $tab === 'aktif' ? 7 : 6 }}" class="text-center text-muted py-5">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2rem"></i>
                            Tidak ada data siswa.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $siswa])
    </div>
</div>

{{-- Sticky Action Bar (muncul saat ada checkbox dicentang) --}}
@if ($tab === 'aktif')
<div class="sticky-action-bar" id="stickyActionBar">
    <span class="selected-count" id="selectedCount">0 siswa dipilih</span>

    <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
        <label class="text-white mb-0" style="font-size:.85rem;white-space:nowrap">Pilih Kelas Tujuan:</label>
        <select class="form-select form-select-sm" id="kelasTujuanBulk" style="min-width:140px">
            <option value="">-- Pilih Kelas --</option>
            @foreach ($kelasList->groupBy('tingkat') as $tingkat => $kList)
                <optgroup label="Tingkat {{ $tingkat }}">
                    @foreach ($kList as $k)
                        <option value="{{ $k->kelas_id }}">{{ $k->label }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <button class="btn btn-sm btn-primary fw-semibold" id="btnPindahkanSekarang">
            <i class="bi bi-arrow-right-circle me-1"></i> Pindahkan
        </button>
        <button class="btn btn-sm btn-outline-light" id="btnBatalBulk">Batal</button>
    </div>
</div>

{{-- Form tersembunyi untuk submit bulk action --}}
<form id="formBulkPindah" method="POST" action="{{ route('admin.akun-siswa.bulk-pindah') }}">
    @csrf
</form>
@endif

@endsection
