<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdatePembinaRequest- validasi form edit pembina.
 */
class UpdatePembinaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:100',
            'is_active'    => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama pembina wajib diisi.',
            'nama_lengkap.max'      => 'Nama pembina maksimal 100 karakter.',
            'is_active.required'    => 'Status wajib dipilih.',
        ];
    }
}
