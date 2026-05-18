{{--
    components/pagination.blade.php
    Pagination custom Bootstrap 5 yang sesuai dengan gaya sistem.
    Dipanggil via: {{ $data->withQueryString()->links('components.pagination') }}

    Menampilkan: << < 1 … 3 4 5 … 15 > >>
    Hanya tampil jika total halaman lebih dari 1.
--}}

@if ($paginator->hasPages())
<nav aria-label="Navigasi halaman">
    <ul class="pagination pagination-sm mb-0 gap-1">

        {{-- Tombol << (halaman pertama) --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link rounded">«</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link rounded" href="{{ $paginator->url(1) }}">«</a>
            </li>
        @endif

        {{-- Tombol < (halaman sebelumnya) --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link rounded">‹</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link rounded" href="{{ $paginator->previousPageUrl() }}">‹</a>
            </li>
        @endif

        {{-- Nomor halaman dengan ellipsis --}}
        @foreach ($elements as $element)
            {{-- Ellipsis "…" --}}
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link rounded border-0 bg-transparent">…</span>
                </li>
            @endif

            {{-- Nomor halaman --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <span class="page-link rounded">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link rounded" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Tombol > (halaman berikutnya) --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link rounded" href="{{ $paginator->nextPageUrl() }}">›</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link rounded">›</span>
            </li>
        @endif

        {{-- Tombol >> (halaman terakhir) --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link rounded" href="{{ $paginator->url($paginator->lastPage()) }}">»</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link rounded">»</span>
            </li>
        @endif

    </ul>
</nav>
@endif
