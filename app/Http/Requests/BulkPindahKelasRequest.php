<?php

namespace App\Http\Requests;

use App\Models\Kelas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

/**
 * BulkPindahKelasRequest- validasi form pemindahan kelas massal siswa.
 *
 * Dipakai di: AkunSiswaController::bulkPindahKelas()
 *
 * Validasi struktural:
 * - siswa_ids array, minimal 1 elemen, semua harus ID siswa valid
 * - kelas_tujuan_id harus ada di tabel kelas
 *
 * Validasi bisnis (withValidator):
 * - Kelas tujuan harus berstatus aktif
 * - Tidak boleh memindahkan siswa alumni (status = 'alumni')
 */
class BulkPindahKelasRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'siswa_ids'          => 'required|array|min:1',
            'siswa_ids.*'        => 'required|integer|exists:siswa,siswa_id',
            'kelas_tujuan_id'    => 'required|integer|exists:kelas,kelas_id',
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_ids.required'       => 'Pilih minimal satu siswa untuk dipindahkan.',
            'siswa_ids.min'            => 'Pilih minimal satu siswa.',
            'siswa_ids.*.exists'       => 'Salah satu siswa yang dipilih tidak valid.',
            'kelas_tujuan_id.required' => 'Kelas tujuan wajib dipilih.',
            'kelas_tujuan_id.exists'   => 'Kelas tujuan yang dipilih tidak valid.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $kelas = Kelas::find($this->input('kelas_tujuan_id'));

            // Kelas tujuan harus aktif
            if ($kelas && ! $kelas->is_active) {
                $v->errors()->add(
                    'kelas_tujuan_id',
                    "Kelas {$kelas->label} sedang nonaktif dan tidak bisa dipilih sebagai tujuan."
                );
            }
        });
    }
}
