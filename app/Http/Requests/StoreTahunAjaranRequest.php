<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreTahunAjaranRequest- validasi form buat tahun ajaran baru.
 */
class StoreTahunAjaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun_mulai'   => 'required|digits:4|integer|min:2000|max:2100',
            'tahun_selesai' => [
                'required',
                'digits:4',
                'integer',
                'min:2000',
                'max:2100',
                'gt:tahun_mulai',
            ],
            'semester' => [
                'required',
                'in:ganjil,genap',
                // Kombinasi tahun_mulai + tahun_selesai + semester harus unik
                Rule::unique('tahun_ajaran', 'semester')
                    ->where('tahun_mulai', $this->input('tahun_mulai'))
                    ->where('tahun_selesai', $this->input('tahun_selesai')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_mulai.required'   => 'Tahun mulai wajib diisi.',
            'tahun_mulai.digits'     => 'Tahun mulai harus 4 digit angka.',
            'tahun_selesai.required' => 'Tahun selesai wajib diisi.',
            'tahun_selesai.digits'   => 'Tahun selesai harus 4 digit angka.',
            'tahun_selesai.gt'       => 'Tahun selesai harus lebih besar dari tahun mulai.',
            'semester.required'      => 'Semester wajib dipilih.',
            'semester.in'            => 'Semester harus ganjil atau genap.',
            'semester.unique'        => "Tahun ajaran {$this->input('tahun_mulai')}/{$this->input('tahun_selesai')} semester " . ucfirst($this->input('semester')) . ' sudah ada.',
        ];
    }
}
