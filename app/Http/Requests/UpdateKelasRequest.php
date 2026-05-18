<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateKelasRequest- validasi form edit kelas.
 *
 * Dipakai di: KelasController::update()
 *
 * Validasi unique kombinasi tingkat + nama_kelas harus mengecualikan
 * record kelas yang sedang diedit (ignore by kelas_id).
 */
class UpdateKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil ID kelas dari route parameter (model binding)
        $kelasId = $this->route('kelas')?->kelas_id;

        return [
            'tingkat'    => 'required|in:10,11,12',
            'nama_kelas' => [
                'required',
                'string',
                'max:20',
                Rule::unique('kelas', 'nama_kelas')
                    ->where('tingkat', $this->input('tingkat'))
                    ->ignore($kelasId, 'kelas_id'),
            ],
            'is_active'  => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'tingkat.required'    => 'Tingkat kelas wajib dipilih.',
            'tingkat.in'          => 'Tingkat kelas harus 10, 11, atau 12.',
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.unique'   => "Kelas {$this->input('tingkat')} {$this->input('nama_kelas')} sudah ada.",
            'is_active.required'  => 'Status wajib dipilih.',
        ];
    }
}
