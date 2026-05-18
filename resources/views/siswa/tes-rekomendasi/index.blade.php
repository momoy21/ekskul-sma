{{-- ═══════════════════════ tes-rekomendasi/index.blade.php ═══════════════ --}}
@extends('layouts.siswa')
@section('title', 'Tes Rekomendasi')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

<div class="bg-white rounded-3 p-4 p-md-5 shadow-sm text-center">

    <i class="bi bi-patch-question-fill text-primary mb-3 d-block" style="font-size:3rem"></i>
    <h5 class="fw-bold mb-1">Tes Rekomendasi Ekstrakurikuler</h5>
    <p class="text-muted mb-4" style="font-size:.9rem">
        Temukan ekskul yang paling cocok dengan menjawab beberapa pertanyaan singkat.
    </p>

    {{-- Stepper --}}
    <div class="tes-stepper mb-4">
        <div class="tes-step {{ $tes ? 'done' : 'active' }}">
            <div class="tes-step-circle">
                @if ($tes) <i class="bi bi-check-lg"></i> @else 1 @endif
            </div>
            <div class="tes-step-label">Bobot Kriteria</div>
        </div>
        <div class="tes-step-line {{ $tes ? 'done' : '' }}"></div>
        <div class="tes-step {{ $tes ? 'done' : '' }}">
            <div class="tes-step-circle">
                @if ($tes) <i class="bi bi-check-lg"></i> @else 2 @endif
            </div>
            <div class="tes-step-label">Soal Tes</div>
        </div>
        <div class="tes-step-line {{ $tes ? 'done' : '' }}"></div>
        <div class="tes-step {{ $tes ? 'active' : '' }}">
            <div class="tes-step-circle">3</div>
            <div class="tes-step-label">Hasil</div>
        </div>
    </div>

    {{-- Info singkat --}}
    <div class="d-flex justify-content-center gap-4 mb-4 text-muted" style="font-size:.83rem">
        <span><i class="bi bi-clock me-1"></i> ±5 menit</span>
        <span><i class="bi bi-list-check me-1"></i> Menyesuaikan soal aktif</span>
        <span><i class="bi bi-trophy me-1"></i> 3 rekomendasi terbaik</span>
    </div>

    @if ($tes && $tes->sudah_submit)
        {{-- Sudah pernah tes --}}
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 text-start" style="font-size:.85rem">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            Kamu sudah mengikuti tes rekomendasi untuk semester ini.
            Hasil tersimpan dan siap digunakan saat pendaftaran.
        </div>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('siswa.tes.hasil') }}" class="btn btn-success px-4">
                <i class="bi bi-trophy me-1"></i> Lihat Hasil
            </a>
            <button type="button" class="btn btn-outline-secondary px-4"
                    data-confirm="Mengulang tes akan menghapus hasil sebelumnya, lanjutkan?"
                    data-confirm-title="Ulang Tes?"
                    data-confirm-type="warning"
                    data-confirm-btn="Ya, Ulang Tes"
                    data-target-form="formResetTes">
                <i class="bi bi-arrow-repeat me-1"></i> Ulang Tes
            </button>
        </div>
        <form id="formResetTes" method="POST" action="{{ route('siswa.tes.reset') }}">
            @csrf @method('DELETE')
        </form>
    @else
        <a href="{{ route('siswa.tes.mulai') }}" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-play-fill me-2"></i> Mulai Tes Sekarang
        </a>
        @if (! $periode)
            <p class="text-muted mt-3" style="font-size:.8rem">
                Belum ada periode aktif, namun kamu tetap bisa mengikuti tes.
            </p>
        @endif
    @endif

</div>
</div>
</div>
@endsection
