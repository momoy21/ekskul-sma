{{--
    components/status-toggle.blade.php
    Tombol toggle status aktif/nonaktif langsung dari baris tabel.
    Mengirim PATCH request ke route toggle-status dengan konfirmasi SweetAlert2.

    Props:
    - $isActive   : boolean- status saat ini
    - $route      : nama route toggle-status
    - $routeParam : model yang jadi parameter route
    - $label      : nama item untuk pesan konfirmasi (opsional)

    Contoh:
    @include('components.status-toggle', [
        'isActive'   => $item->is_active,
        'route'      => 'admin.kelas.toggle-status',
        'routeParam' => $item,
        'label'      => $item->nama_kelas,
    ])
--}}

<form method="POST" action="{{ route($route, $routeParam) }}" class="d-inline">
    @csrf
    @method('PATCH')

    @if ($isActive)
        <button
            type="submit"
            class="btn-toggle-status badge-aktif"
            data-confirm="Nonaktifkan {{ $label ?? 'item ini' }}? Data ini tidak akan muncul di menu manapun."
            data-confirm-title="Nonaktifkan?"
            data-confirm-type="warning"
            data-confirm-btn="Ya, Nonaktifkan"
        >
            Aktif
        </button>
    @else
        <button
            type="submit"
            class="btn-toggle-status badge-nonaktif"
            data-confirm="Aktifkan kembali {{ $label ?? 'item ini' }}?"
            data-confirm-title="Aktifkan?"
            data-confirm-type="question"
            data-confirm-btn="Ya, Aktifkan"
        >
            Nonaktif
        </button>
    @endif
</form>
