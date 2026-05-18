<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateEkskulRequest- validasi form edit ekskul.
 *
 * Dipakai di: EkskulController::update()
 */
class UpdateEkskulRequest extends FormRequest
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
            'is_active'           => 'required|in:0,1',
            // kuota_minimal tidak ada- selalu 10, tidak bisa di-edit
        ];
    }

    public function messages(): array
    {
        return [
            'nama_ekskul.required'        => 'Nama ekstrakurikuler wajib diisi.',
            'kategori_ekskul_id.required' => 'Kategori wajib dipilih.',
            'pembina_ids.required'        => 'Pilih minimal satu pembina.',
            'pembina_ids.min'             => 'Pilih minimal satu pembina.',
            'pembina_ids.*.exists'        => 'Salah satu pembina yang dipilih tidak valid.',
            'hari_pelaksanaan.required'   => 'Hari pelaksanaan wajib dipilih.',
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
            'is_active.required'          => 'Status wajib dipilih.',
        ];
    }
}
