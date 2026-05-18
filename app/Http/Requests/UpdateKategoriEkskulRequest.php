<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateKategoriEkskulRequest- validasi form edit kategori ekskul.
 */
class UpdateKategoriEkskulRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $kategoriId = $this->route('kategoriEkskul')?->kategori_ekskul_id;

        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:80',
                Rule::unique('kategori_ekskul', 'nama_kategori')
                    ->ignore($kategoriId, 'kategori_ekskul_id'),
            ],
            'is_active' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Kategori dengan nama ini sudah ada.',
            'is_active.required'     => 'Status wajib dipilih.',
        ];
    }
}
