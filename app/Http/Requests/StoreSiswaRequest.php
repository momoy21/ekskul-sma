<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreSiswaRequest- validasi form tambah akun siswa baru.
 *
 * Dipakai di: AkunSiswaController::store()
 *
 * Catatan:
 * - NISN harus tepat 10 digit angka dan belum terdaftar di tabel siswa
 * - NISN juga dipakai sebagai username di tabel pengguna, jadi cek duplikasi di keduanya
 * - Tanggal lahir akan otomatis jadi password default (format DDMMYYYY)
 */
class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya admin yang bisa akses route ini (sudah dijaga middleware role.admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'nisn'          => [
                'required',
                'digits:10',                      // tepat 10 angka
                'unique:siswa,nisn',              // belum ada di tabel siswa
                'unique:pengguna,username',       // belum ada sebagai username
            ],
            'nama_lengkap'  => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id'      => 'required|exists:kelas,kelas_id',
        ];
    }

    public function messages(): array
    {
        return [
            'nisn.required'         => 'NISN wajib diisi.',
            'nisn.digits'           => 'NISN harus tepat 10 digit angka.',
            'nisn.unique'           => 'NISN ini sudah terdaftar di sistem.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max'      => 'Nama lengkap maksimal 100 karakter.',
            'tanggal_lahir.required'=> 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date'    => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before'  => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.required'=> 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in'      => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'kelas_id.required'     => 'Kelas wajib dipilih.',
            'kelas_id.exists'       => 'Kelas yang dipilih tidak valid.',
        ];
    }
}
