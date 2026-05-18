<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreKelasRequest- validasi form tambah kelas baru.
 *
 * Dipakai di: KelasController::store()
 *
 * Kombinasi tingkat + nama_kelas harus unik (sesuai UNIQUE constraint di DB).
 * Misal: tidak boleh ada dua kelas "10 A".
 */
class StoreKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tingkat'    => 'required|in:10,11,12',
            'nama_kelas' => [
                'required',
                'string',
                'max:20',
                // Cek kombinasi tingkat + nama_kelas belum ada
                Rule::unique('kelas', 'nama_kelas')
                    ->where('tingkat', $this->input('tingkat')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tingkat.required'    => 'Tingkat kelas wajib dipilih.',
            'tingkat.in'          => 'Tingkat kelas harus 10, 11, atau 12.',
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.max'      => 'Nama kelas maksimal 20 karakter.',
            'nama_kelas.unique'   => "Kelas {$this->input('tingkat')} {$this->input('nama_kelas')} sudah ada.",
        ];
    }
}
