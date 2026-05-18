<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreSoalRequest- validasi form tambah soal rekomendasi baru.
 *
 * Dipakai di: SoalController::store()
 *
 * Setiap soal harus dikaitkan ke minimal 1 ekskul dan 1 kriteria aktif.
 * kode_soal tidak divalidasi di sini karena di-generate otomatis di controller.
 */
class StoreSoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kriteria_id'  => 'required|exists:kriteria,kriteria_id',
            'teks_soal'    => 'required|string|max:500',
            // Minimal satu ekskul wajib dipilih
            'ekskul_ids'   => 'required|array|min:1',
            'ekskul_ids.*' => 'required|exists:ekskul,ekskul_id',
        ];
    }

    public function messages(): array
    {
        return [
            'kriteria_id.required'  => 'Kriteria wajib dipilih.',
            'kriteria_id.exists'    => 'Kriteria yang dipilih tidak valid.',
            'teks_soal.required'    => 'Teks soal wajib diisi.',
            'teks_soal.max'         => 'Teks soal maksimal 500 karakter.',
            'ekskul_ids.required'   => 'Pilih minimal satu ekstrakurikuler yang relevan dengan soal ini.',
            'ekskul_ids.min'        => 'Pilih minimal satu ekstrakurikuler.',
            'ekskul_ids.*.exists'   => 'Salah satu ekstrakurikuler yang dipilih tidak valid.',
        ];
    }
}
