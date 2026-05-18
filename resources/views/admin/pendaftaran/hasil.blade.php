@extends('layouts.app')
@section('title', 'Hasil Pendaftaran')

@section('content')
<div class="table-card">
    <div class="card-header">
        @include('components.table-header', [
            'searchPlaceholder' => 'Cari nama siswa atau NISN...',
            'searchName'        => 'search',
            'filters' => [
                ['name' => 'status', 'placeholder' => 'Filter Status',
                 'options' => ['not_submitted' => 'Belum Submit', 'submitted' => 'Submitted', 'finalized' => 'Finalized']],
            ],
        ])
    </div>

    {{-- Info periode --}}
    @if ($periode)
        <div class="card-body border-bottom py-2 px-4" style="background-color:#f8f9fa;font-size:.85rem">
            <div class="row g-2">
                <div class="col-md-6">
                    <span class="text-muted">Tahun Ajaran:</span> <strong>{{ optional($periode->tahunAjaran)->label }}</strong>
                    &nbsp;·&nbsp;
                    <span class="text-muted">Semester:</span> <strong>{{ ucfirst($periode->semester) }}</strong>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted">Total Data:</span> <strong>{{ $pendaftaran->total() }} siswa</strong>
                </div>
            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:15%">Nama Siswa</th>
                    <th style="width:10%">NISN</th>
                    <th style="width:25%">Pendaftaran Awal</th>
                    <th style="width:20%">Pemilihan Ulang</th>
                    <th style="width:20%">Hasil Final</th>
                    <th style="width:5%" class="text-center">Status</th>
                    <th style="width:8%" class="text-center">Preview TTD</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendaftaran as $siswa)
                    @php
                        $daftar = $siswa->pendaftaran->first();
                        $statusEfektif = in_array($daftar?->status, ['submitted', 'finalized']) ? $daftar->status : 'not_submitted';
                    @endphp
                    <tr>
                        <td class="align-middle">{{ $pendaftaran->firstItem() + $loop->index }}</td>
                        <td class="align-middle fw-semibold">{{ $siswa->nama_lengkap }}</td>
                        <td class="align-middle font-monospace" style="font-size:.8rem">{{ $siswa->nisn }}</td>

                        {{-- Pendaftaran Awal --}}
                        <td class="align-middle">
                            @if (! $daftar || $daftar->pilihanEkskul->isEmpty())
                                <span class="text-muted">-</span>
                            @else
                                <ul class="list-unstyled mb-0" style="font-size:.8rem">
                                    @foreach ($daftar->pilihanEkskul as $pilihan)
                                        <li class="mb-1">{{ $pilihan->ekskul->nama_ekskul }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>

                        {{-- Pemilihan Ulang --}}
                        <td class="align-middle">
                            @php
                                $adaPerubahan = false;
                                if ($daftar) {
                                    foreach ($daftar->pilihanEkskul as $p) {
                                        if ($p->ekskul_cadangan_id) {
                                            $adaPerubahan = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            @if (! $adaPerubahan)
                                <span class="text-muted">-</span>
                            @else
                                <ul class="list-unstyled mb-0" style="font-size:.8rem">
                                    @foreach ($daftar->pilihanEkskul as $pilihan)
                                        @if ($pilihan->ekskul_cadangan_id)
                                            <li class="mb-1">
                                                <i class="bi bi-arrow-right text-info"></i>
                                                <strong>{{ $pilihan->ekskulCadangan->nama_ekskul ?? '-' }}</strong>
                                                Cadangan
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </td>

                        {{-- Hasil Final --}}
                        <td class="align-middle">
                            @if ($statusEfektif === 'finalized')
                                <ul class="list-unstyled mb-0" style="font-size:.8rem">
                                    @foreach ($daftar->pilihanEkskul as $pilihan)
                                        <li class="mb-1">
                                            <strong>{{ $pilihan->ekskulFinal->nama_ekskul ?? $pilihan->ekskul->nama_ekskul }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif ($statusEfektif === 'not_submitted')
                                <span class="badge bg-secondary" style="font-size:.75rem">Belum Submit</span>
                            @else
                                <span class="badge bg-secondary" style="font-size:.75rem">Menunggu</span>
                            @endif
                        </td>

                        {{-- Status Pendaftaran --}}
                        <td class="align-middle text-center">
                            @if ($statusEfektif === 'finalized')
                                <span class="badge bg-success">Finalized</span>
                            @elseif ($statusEfektif === 'submitted')
                                <span class="badge bg-info">Submitted</span>
                            @else
                                <span class="badge bg-secondary">Belum Submit</span>
                            @endif
                        </td>

                        {{-- Preview tanda tangan orang tua --}}
                        <td class="align-middle text-center">
                            @if ($daftar && filled($daftar->tanda_tangan_ortu))
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPreviewTtd"
                                    data-signature="{{ $daftar->tanda_tangan_ortu }}"
                                    data-siswa="{{ $siswa->nama_lengkap }}"
                                    data-waktu="{{ optional($daftar->waktu_ttd)->format('d/m/Y H:i') ?? '-' }}"
                                >
                                    Preview
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2rem"></i>
                            {{ $periode ? 'Tidak ada data siswa yang sesuai filter.' : 'Periode pendaftaran belum tersedia.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 px-4">
        @include('components.table-footer', ['data' => $pendaftaran])
    </div>
</div>

<div class="modal fade" id="modalPreviewTtd" tabindex="-1" aria-labelledby="modalPreviewTtdLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalPreviewTtdLabel">Preview Tanda Tangan Orang Tua</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-2">
                    Siswa: <strong id="ttdSiswaLabel">-</strong> · Waktu TTD: <strong id="ttdWaktuLabel">-</strong>
                </div>
                <div class="border rounded p-3 bg-light text-center">
                    <img
                        id="previewTtdImage"
                        src=""
                        alt="Preview tanda tangan orang tua"
                        style="max-width:100%; max-height:300px; object-fit:contain;"
                    >
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('modalPreviewTtd');
        if (! modal) return;

        modal.addEventListener('show.bs.modal', event => {
            const trigger = event.relatedTarget;
            const img = document.getElementById('previewTtdImage');
            const siswaLabel = document.getElementById('ttdSiswaLabel');
            const waktuLabel = document.getElementById('ttdWaktuLabel');

            if (! trigger || ! img || !siswaLabel || !waktuLabel) return;

            const rawSignature = trigger.getAttribute('data-signature') || '';
            img.src = rawSignature.startsWith('data:image/') ? rawSignature : '';
            siswaLabel.textContent = trigger.getAttribute('data-siswa') || '-';
            waktuLabel.textContent = trigger.getAttribute('data-waktu') || '-';
        });
    });
</script>
@endpush
