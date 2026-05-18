<?php

namespace App\Http\Requests;

use App\Models\SoalRekomendasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

/**
 * SubmitTesRekomendasiRequest- validasi submit tes rekomendasi ekskul.
 *
 * Dipakai di: TesRekomendasiController::submit()
 *
 * Validasi struktural:
 * - bobot C1–C5 masing-masing wajib, skala 1–5
 * - Setiap soal aktif wajib dijawab, nilai 1–5
 *
 * Validasi bisnis (withValidator):
 * - Jumlah soal yang dijawab harus sama dengan soal aktif di DB
 *   (mencegah manipulasi- siswa tidak boleh melewati soal)
 */
class SubmitTesRekomendasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil semua soal aktif untuk generate rules dinamis
        $soalAktifIds = SoalRekomendasi::aktif()->pluck('soal_id');

        $soalRules = [];
        foreach ($soalAktifIds as $soalId) {
            // Setiap soal wajib dijawab dengan nilai 1–5
            $soalRules["jawaban.{$soalId}"] = 'required|integer|min:1|max:5';
        }

        return array_merge([
            'bobot_c1' => 'required|integer|min:1|max:5',
            'bobot_c2' => 'required|integer|min:1|max:5',
            'bobot_c3' => 'required|integer|min:1|max:5',
            'bobot_c4' => 'required|integer|min:1|max:5',
            'bobot_c5' => 'required|integer|min:1|max:5',
        ], $soalRules);
    }

    public function messages(): array
    {
        return [
            'bobot_c1.required' => 'Bobot untuk Minat wajib diisi.',
            'bobot_c2.required' => 'Bobot untuk Jadwal wajib diisi.',
            'bobot_c3.required' => 'Bobot untuk Biaya Tambahan wajib diisi.',
            'bobot_c4.required' => 'Bobot untuk Fasilitas wajib diisi.',
            'bobot_c5.required' => 'Bobot untuk Intensitas Kegiatan wajib diisi.',
            'bobot_c1.min'      => 'Bobot minimal bernilai 1.',
            'bobot_c1.max'      => 'Bobot maksimal bernilai 5.',
            // Pesan generik untuk semua soal
            'jawaban.*.required'=> 'Semua soal wajib dijawab.',
            'jawaban.*.integer' => 'Jawaban harus berupa angka.',
            'jawaban.*.min'     => 'Jawaban minimal bernilai 1.',
            'jawaban.*.max'     => 'Jawaban maksimal bernilai 5.',
        ];
    }

    /**
     * Validasi tambahan: jumlah jawaban harus sama persis dengan soal aktif.
     * Mencegah kasus di mana siswa hanya mengirim sebagian soal.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $soalAktifCount = SoalRekomendasi::aktif()->count();
            $jawabanCount   = count(array_filter($this->input('jawaban', [])));

            if ($jawabanCount < $soalAktifCount) {
                $v->errors()->add(
                    'jawaban',
                    "Semua soal wajib dijawab. Kamu baru menjawab {$jawabanCount} dari {$soalAktifCount} soal."
                );
            }
        });
    }
}
