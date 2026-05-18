<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateSiswaRequest- validasi form edit data siswa.
 *
 * Dipakai di: AkunSiswaController::update()
 *
 * Perbedaan dari StoreSiswaRequest:
 * - NISN tidak bisa diubah, jadi tidak ada validasi NISN
 * - Password bersifat opsional (kosong = tidak ganti)
 * - Tambah validasi is_active (status akun)
 */
class UpdateSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id'      => 'required|exists:kelas,kelas_id',
            'status'        => 'required|in:aktif,alumni',
            // Password opsional- hanya diupdate jika diisi
            'password_baru' => 'nullable|string|min:8|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max'      => 'Nama lengkap maksimal 100 karakter.',
            'tanggal_lahir.required'=> 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before'  => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.required'=> 'Jenis kelamin wajib dipilih.',
            'kelas_id.required'     => 'Kelas wajib dipilih.',
            'kelas_id.exists'       => 'Kelas yang dipilih tidak valid.',
            'status.required'       => 'Status wajib dipilih.',
            'status.in'             => 'Status harus aktif atau alumni.',
            'password_baru.min'     => 'Password baru minimal 8 karakter.',
        ];
    }
}
