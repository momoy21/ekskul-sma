<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreKategoriEkskulRequest- validasi form tambah kategori ekskul.
 */
class StoreKategoriEkskulRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // nama_kategori bersifat UNIQUE di tabel
            'nama_kategori' => 'required|string|max:80|unique:kategori_ekskul,nama_kategori',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max'      => 'Nama kategori maksimal 80 karakter.',
            'nama_kategori.unique'   => 'Kategori dengan nama ini sudah ada.',
        ];
    }
}
