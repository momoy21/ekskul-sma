<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreEkskulRequest- validasi form tambah ekskul baru.
 *
 * Dipakai di: EkskulController::store()
 */
class StoreEkskulRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_ekskul'         => 'required|string|max:100',
            'kategori_ekskul_id'  => 'required|exists:kategori_ekskul,kategori_ekskul_id',
            'pembina_ids'         => 'required|array|min:1',
            'pembina_ids.*'       => 'required|exists:pembina,pembina_id',
            'hari_pelaksanaan'    => 'required|in:Senin,Selasa,Kamis,Jumat',
            'lokasi'              => 'required|string|max:100',
            'biaya_tambahan'      => 'required|integer|min:1|max:5',
            'fasilitas_level'     => 'required|integer|min:1|max:5',
            'intensitas_kegiatan' => 'required|integer|min:1|max:5',
            'deskripsi_kegiatan'  => 'nullable|string|max:2000',
            'foto'                => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_ekskul.required'        => 'Nama ekstrakurikuler wajib diisi.',
            'nama_ekskul.max'             => 'Nama ekstrakurikuler maksimal 100 karakter.',
            'kategori_ekskul_id.required' => 'Kategori wajib dipilih.',
            'kategori_ekskul_id.exists'   => 'Kategori yang dipilih tidak valid.',
            'pembina_ids.required'        => 'Pilih minimal satu pembina.',
            'pembina_ids.min'             => 'Pilih minimal satu pembina.',
            'pembina_ids.*.exists'        => 'Salah satu pembina yang dipilih tidak valid.',
            'hari_pelaksanaan.required'   => 'Hari pelaksanaan wajib dipilih.',
            'hari_pelaksanaan.in'         => 'Hari pelaksanaan harus Senin, Selasa, Kamis, atau Jumat.',
            'lokasi.required'             => 'Lokasi wajib diisi.',
            'biaya_tambahan.required'     => 'Biaya tambahan wajib dipilih.',
            'biaya_tambahan.integer'      => 'Biaya tambahan harus berupa angka.',
            'biaya_tambahan.min'          => 'Biaya tambahan minimal 1.',
            'biaya_tambahan.max'          => 'Biaya tambahan maksimal 5.',
            'fasilitas_level.required'    => 'Fasilitas wajib dipilih.',
            'fasilitas_level.integer'     => 'Fasilitas harus berupa angka.',
            'fasilitas_level.min'         => 'Fasilitas minimal 1.',
            'fasilitas_level.max'         => 'Fasilitas maksimal 5.',
            'intensitas_kegiatan.required'=> 'Intensitas kegiatan wajib dipilih.',
            'intensitas_kegiatan.integer' => 'Intensitas kegiatan harus berupa angka.',
            'intensitas_kegiatan.min'     => 'Intensitas kegiatan minimal 1.',
            'intensitas_kegiatan.max'     => 'Intensitas kegiatan maksimal 5.',
            'foto.image'                  => 'File harus berupa gambar (JPG atau PNG).',
            'foto.mimes'                  => 'Format foto harus JPG atau PNG.',
            'foto.max'                    => 'Ukuran foto maksimal 5MB.',
        ];
    }
}
