@extends('layouts.siswa')
@section('title', 'Tes Rekomendasi')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

@if ($errors->any())
    <div class="alert alert-danger mb-3" style="font-size:.85rem">
        <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('siswa.tes.submit') }}" id="formTes">
@csrf

{{-- Header dengan stepper --}}
<div class="bg-white rounded-3 p-4 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0" id="stepTitle">Tahap 1 dari 2- Tentukan Prioritas Kamu</h6>
            <p class="text-muted mb-0" style="font-size:.82rem" id="stepDesc">
                Seberapa penting hal-hal berikut bagimu? (1 = sangat tidak penting, 2 = tidak penting, 3 = netral, 4 = penting, 5 = sangat penting)
            </p>
        </div>
        <div class="tes-stepper" style="gap:0">
            <div class="tes-step active" id="dot1">
                <div class="tes-step-circle" style="width:36px;height:36px;font-size:.9rem">1</div>
            </div>
            <div class="tes-step-line" id="line12" style="max-width:40px"></div>
            <div class="tes-step" id="dot2">
                <div class="tes-step-circle" style="width:36px;height:36px;font-size:.9rem">2</div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ TAHAP 1: Bobot Kriteria ══════════════ --}}
<div id="tahap1">
    @foreach ($kriteriaList as $k)
        <div class="soal-card">
            <div class="soal-nomor">{{ $k->kode }}- {{ $k->nama_kriteria }}</div>
            <p class="mb-3" style="font-size:.9rem;line-height:1.5">{{ $k->deskripsi_siswa }}</p>

            <div class="likert-group" role="group">
                @for ($val = 1; $val <= 5; $val++)
                    <div class="likert-option">
                        <input type="radio" name="bobot_{{ strtolower($k->kode) }}"
                               id="{{ $k->kode }}_{{ $val }}" value="{{ $val }}"
                               {{ old('bobot_'.strtolower($k->kode), $bobotLama[$k->kode] ?? null) == $val ? 'checked' : '' }}
                               required>
                        <label for="{{ $k->kode }}_{{ $val }}">{{ $val }}</label>
                    </div>
                @endfor
            </div>
            <div class="d-flex justify-content-between text-muted mt-1" style="font-size:.72rem; max-width:486px;">
                <span>Sangat tidak penting</span>
                <span>Sangat penting</span>
            </div>

            @error("bobot_{$k->kode}")
                <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
            @enderror
        </div>
    @endforeach

    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('siswa.tes.index') }}" class="btn btn-outline-secondary px-4">
            <i class="bi bi-x-lg me-1"></i> Batal
        </a>
        <button type="button" class="btn btn-primary px-4" id="btnLanjutSoal">
            Lanjut ke Soal <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>

{{-- ══════════════ TAHAP 2: Soal Tes ══════════════ --}}
<div id="tahap2" style="display:none">
    @foreach ($soalPerKriteria as $kriteriaId => $soalList)
        @php $k = $kriteriaList->firstWhere('kriteria_id', $kriteriaId); @endphp
        @if ($k)
            <div class="mb-2 mt-4">
                <span class="badge bg-primary px-3 py-2" style="font-size:.8rem">
                    {{ $k->kode }}- {{ $k->nama_kriteria }}
                </span>
            </div>
        @endif

        @foreach ($soalList as $soal)
            <div class="soal-card">
                <div class="soal-nomor">Soal {{ $loop->parent->index * 99 + $loop->index + 1 }} · {{ $soal->kode_soal }}</div>
                <p class="mb-3" style="font-size:.9rem;line-height:1.5">{{ $soal->teks_soal }}</p>

                <div class="likert-group" role="group">
                    @for ($val = 1; $val <= 5; $val++)
                        <div class="likert-option">
                            <input type="radio"
                                   name="jawaban[{{ $soal->soal_id }}]"
                                   id="s{{ $soal->soal_id }}_{{ $val }}"
                                   value="{{ $val }}"
                                   {{ old("jawaban.{$soal->soal_id}", $jawabanLama[$soal->soal_id] ?? null) == $val ? 'checked' : '' }}
                                   required>
                            <label for="s{{ $soal->soal_id }}_{{ $val }}">{{ $val }}</label>
                        </div>
                    @endfor
                </div>
                <div class="d-flex justify-content-between text-muted mt-1" style="font-size:.72rem; max-width:486px;">
                    <span>Sangat Tidak Setuju</span>
                    <span>Sangat Setuju</span>
                </div>

                @error("jawaban.{$soal->soal_id}")
                    <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
    @endforeach

    <div class="d-flex justify-content-between mt-3">
        <button type="button" class="btn btn-outline-secondary px-4" id="btnKembaliBobot">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
        <button type="submit" class="btn btn-success px-4">
            <i class="bi bi-check-circle me-1"></i> Submit Tes
        </button>
    </div>
</div>

</form>
</div>
</div>
@endsection

@push('scripts')
<script>
    const tahap1   = document.getElementById('tahap1');
    const tahap2   = document.getElementById('tahap2');
    const dot1     = document.getElementById('dot1');
    const dot2     = document.getElementById('dot2');
    const line12   = document.getElementById('line12');
    const stepTitle = document.getElementById('stepTitle');
    const stepDesc  = document.getElementById('stepDesc');

    // Lanjut ke tahap 2
    document.getElementById('btnLanjutSoal').addEventListener('click', () => {
        // Cek semua bobot sudah diisi
        const kodeList = ['c1','c2','c3','c4','c5'];
        const belumDiisi = kodeList.filter(k =>
            ! document.querySelector(`[name="bobot_${k}"]:checked`)
        );

        if (belumDiisi.length > 0) {
            Swal.fire('Belum Lengkap', 'Isi semua bobot kriteria sebelum melanjutkan.', 'warning');
            return;
        }

        tahap1.style.display = 'none';
        tahap2.style.display = 'block';

        dot1.className = 'tes-step done';
        dot1.querySelector('.tes-step-circle').innerHTML = '<i class="bi bi-check-lg"></i>';
        line12.classList.add('done');
        dot2.classList.add('active');

        stepTitle.textContent = 'Tahap 2 dari 2- Jawab Soal Berikut';
        stepDesc.textContent  = 'Nyatakan tingkat persetujuanmu (1 = Sangat Tidak Setuju, 2 = Tidak Setuju, 3 = Netral, 4 = Setuju, 5 = Sangat Setuju)';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Kembali ke tahap 1
    document.getElementById('btnKembaliBobot').addEventListener('click', () => {
        tahap2.style.display = 'none';
        tahap1.style.display = 'block';

        dot1.className = 'tes-step active';
        dot1.querySelector('.tes-step-circle').innerHTML = '1';
        line12.classList.remove('done');
        dot2.classList.remove('active');

        stepTitle.textContent = 'Tahap 1 dari 2- Tentukan Prioritas Kamu';
        stepDesc.textContent  = 'Seberapa penting hal-hal berikut bagimu? (1 = sangat tidak penting, 2 = tidak penting, 3 = netral, 4 = penting, 5 = sangat penting)';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Konfirmasi sebelum submit tes
    document.getElementById('formTes').addEventListener('submit', function (e) {
        const totalSoal   = document.querySelectorAll('[name^="jawaban["]').length / 5;
        const soalDijawab = new Set(
            Array.from(document.querySelectorAll('[name^="jawaban["]:checked'))
                .map(el => el.name)
        ).size;

        if (soalDijawab < totalSoal) {
            e.preventDefault();
            Swal.fire('Belum Lengkap',
                `Kamu baru menjawab ${soalDijawab} dari ${totalSoal} soal. Jawab semua soal sebelum submit.`,
                'warning');
        }
    });

    // Jika ada error validasi, tampilkan tahap yang sesuai
    @if ($errors->has('jawaban') || $errors->hasAny(array_map(fn($i) => "jawaban.$i", range(1, 100))))
        document.getElementById('btnLanjutSoal').click();
    @endif
</script>
@endpush
