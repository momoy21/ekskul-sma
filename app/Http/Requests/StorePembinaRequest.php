<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePembinaRequest- validasi form tambah pembina.
 */
class StorePembinaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama pembina wajib diisi.',
            'nama_lengkap.max'      => 'Nama pembina maksimal 100 karakter.',
        ];
    }
}
