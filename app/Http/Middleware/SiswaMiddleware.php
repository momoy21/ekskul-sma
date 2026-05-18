<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SiswaMiddleware- memastikan pengguna yang login adalah siswa yang masih aktif.
 *
 * Ada dua kondisi yang di-blokir di sini:
 * 1. Role bukan siswa (misal admin mencoba akses /siswa/...)
 * 2. Siswa aktif = false- terjadi ketika siswa jadi alumni setelah tahun ajaran baru dibuat.
 *    Akun alumni di-set is_active = 0 di tabel pengguna, dan status = 'alumni' di tabel siswa.
 *
 * Kondisi kedua penting: alumni tidak boleh login sama sekali, tapi kita cek di sini
 * sebagai lapisan kedua (pertama sudah dicek di LoginController sebelum session dibuat).
 */
class SiswaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek role dulu
        if (session('role') !== 'siswa') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            session()->flush();

            return redirect()
                ->route('login')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Cek status aktif- siswa yang sudah alumni tidak boleh lanjut
        // is_active di session di-set saat login dari kolom pengguna.is_active
        if (! session('is_active')) {
            session()->flush();

            return redirect()
                ->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi guru koordinator.');
        }

        return $next($request);
    }
}
