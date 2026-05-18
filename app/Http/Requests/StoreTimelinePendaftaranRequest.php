<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreTimelinePendaftaranRequest
 *
 * tanggal_pemilihan_ulang WAJIB diisi.
 * Jam otomatis di-hardcode di model PeriodePendaftaran:
 *   - Pendaftaran tutup jam 11:00
 *   - Pengumuman + pemilihan ulang mulai jam 11:30 (hari yang sama dengan tutup)
 *   - Pemilihan ulang berakhir jam 23:59 di tanggal_pemilihan_ulang
 *
 * Jadi admin hanya perlu isi 3 tanggal, tidak perlu isi jam.
 */
class StoreTimelinePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_buka'            => 'required|date',
            'tanggal_tutup'           => 'required|date|after_or_equal:tanggal_buka',
            // Wajib diisi, minimal hari yang sama dengan tanggal_tutup
            // (pemilihan ulang bisa di hari yang sama, berakhir jam 23:59)
            'tanggal_pemilihan_ulang' => 'required|date|after_or_equal:tanggal_tutup',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_buka.required'                => 'Tanggal buka pendaftaran wajib diisi.',
            'tanggal_tutup.required'               => 'Tanggal tutup pendaftaran wajib diisi.',
            'tanggal_tutup.after_or_equal'         => 'Tanggal tutup harus sama atau setelah tanggal buka.',
            'tanggal_pemilihan_ulang.required'     => 'Batas akhir pemilihan ulang wajib diisi.',
            'tanggal_pemilihan_ulang.after_or_equal' => 'Batas akhir pemilihan ulang harus sama atau setelah tanggal penutupan.',
        ];
    }
}
