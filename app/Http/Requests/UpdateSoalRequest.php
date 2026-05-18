<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateSoalRequest- validasi form edit soal rekomendasi.
 *
 * Dipakai di: SoalController::update()
 *
 * Perbedaan dari StoreSoalRequest:
 * - Tambah validasi is_active
 * - kode_soal tidak bisa diubah (tidak ada di form, tidak perlu divalidasi)
 */
class UpdateSoalRequest extends FormRequest
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
            'ekskul_ids'   => 'required|array|min:1',
            'ekskul_ids.*' => 'required|exists:ekskul,ekskul_id',
            'is_active'    => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'kriteria_id.required' => 'Kriteria wajib dipilih.',
            'teks_soal.required'   => 'Teks soal wajib diisi.',
            'teks_soal.max'        => 'Teks soal maksimal 500 karakter.',
            'ekskul_ids.required'  => 'Pilih minimal satu ekstrakurikuler yang relevan.',
            'ekskul_ids.min'       => 'Pilih minimal satu ekstrakurikuler.',
            'is_active.required'   => 'Status wajib dipilih.',
        ];
    }
}
