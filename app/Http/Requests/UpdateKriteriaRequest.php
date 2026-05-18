<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateKriteriaRequest- validasi form edit kriteria SAW.
 *
 * Dipakai di: KriteriaController::update()
 *
 * Yang boleh diedit: nama_kriteria, deskripsi_siswa, is_active.
 * kode dan tipe_atribut TIDAK boleh diubah- tidak ada di form,
 * tidak perlu divalidasi, dan di controller tidak di-update.
 */
class UpdateKriteriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_kriteria'   => 'required|string|max:80',
            'deskripsi_siswa' => 'nullable|string|max:500',
            'is_active'       => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kriteria.required' => 'Nama kriteria wajib diisi.',
            'nama_kriteria.max'      => 'Nama kriteria maksimal 80 karakter.',
            'deskripsi_siswa.max'    => 'Deskripsi siswa maksimal 500 karakter.',
            'is_active.required'     => 'Status wajib dipilih.',
        ];
    }
}
