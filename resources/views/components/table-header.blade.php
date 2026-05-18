{{--
    components/table-header.blade.php
    Header standar untuk semua halaman index master data admin.
    Berisi: search input + filter select(s) + tombol tambah.

    Props:
    - $searchPlaceholder  : teks placeholder search (default: "Cari data...")
    - $searchName         : nama param query string (default: "search")
    - $createRoute        : nama route untuk tombol tambah (opsional)
    - $createLabel        : label tombol tambah (default: "Tambah Data")
    - $filters            : array filter tambahan [['name' => ..., 'options' => [...], 'placeholder' => ...]]

    Contoh pemakaian:
    @include('components.table-header', [
        'searchPlaceholder' => 'Cari nama atau NISN...',
        'createRoute'       => 'admin.akun-siswa.create',
        'createLabel'       => 'Tambah Siswa',
        'filters' => [
            ['name' => 'tingkat', 'placeholder' => 'Filter Tingkat', 'options' => [10 => '10', 11 => '11', 12 => '12']],
            ['name' => 'status',  'placeholder' => 'Filter Status',  'options' => [1 => 'Aktif', 0 => 'Nonaktif']],
        ],
    ])
--}}

<div class="row g-2 align-items-center">

    {{-- Search input --}}
    <div class="col-12 col-md-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted" style="font-size:.85rem"></i>
            </span>
            <input
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="{{ $searchPlaceholder ?? 'Cari data...' }}"
                value="{{ request($searchName ?? 'search') }}"
                data-live-search
                data-filter-name="{{ $searchName ?? 'search' }}"
                style="font-size:.875rem"
            >
        </div>
    </div>

    {{-- Filter dinamis --}}
    @foreach ($filters ?? [] as $filter)
        <div class="col-6 col-md-3 col-lg-auto">
            <select
                class="form-select"
                data-filter-select
                data-filter-name="{{ $filter['name'] }}"
                style="font-size:.875rem;padding-right:2.5rem"
            >
                <option value="">{{ $filter['placeholder'] ?? 'Filter' }}- Semua</option>
                @foreach ($filter['options'] as $val => $label)
                    <option value="{{ $val }}" {{ request($filter['name']) === (string)$val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    @endforeach

    {{-- Tombol tambah (opsional) --}}
    @if (isset($createRoute))
        <div class="col-12 col-md-auto ms-md-auto">
            <a href="{{ route($createRoute) }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i>
                <span>{{ $createLabel ?? 'Tambah Data' }}</span>
            </a>
        </div>
    @endif

</div>
