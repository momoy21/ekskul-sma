{{--
    components/table-footer.blade.php
    Footer standar tabel: info "Menampilkan X-Y dari Z data" + komponen pagination.

    Props:
    - $data : hasil paginate() dari Eloquent

    Contoh:
    @include('components.table-footer', ['data' => $siswa])
--}}

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">
        @if ($data->total() > 0)
            Menampilkan
            <strong>{{ $data->firstItem() }}</strong>–<strong>{{ $data->lastItem() }}</strong>
            dari <strong>{{ $data->total() }}</strong> data
        @else
            Tidak ada data yang ditemukan
        @endif
    </small>

    {{ $data->withQueryString()->links('components.pagination') }}
</div>
