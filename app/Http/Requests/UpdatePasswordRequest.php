<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdatePasswordRequest- validasi form ganti password.
 *
 * Dipakai di:
 * - Admin\DashboardController::updatePassword()
 * - Siswa\DashboardController::updatePassword()
 *
 * Sistem ini tidak meminta password lama- sesuai rancangan UI.
 * Password baru minimal 8 karakter, maksimal 255 (batas bcrypt).
 */
class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_baru' => 'required|string|min:8|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min'      => 'Password baru minimal 8 karakter.',
            'password_baru.max'      => 'Password terlalu panjang.',
        ];
    }
}
