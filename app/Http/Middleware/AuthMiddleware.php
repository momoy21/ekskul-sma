<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuthMiddleware- cek apakah pengguna sudah login via session.
 *
 * Dipasang sebagai lapisan pertama di semua route yang butuh autentikasi,
 * baik untuk admin maupun siswa. Middleware role (Admin/Siswa) baru
 * dicek setelah middleware ini lolos.
 *
 * Session yang dicek: 'pengguna_id'- di-set saat LoginController berhasil.
 */
class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('pengguna_id')) {
            // Kalau request AJAX/JSON, balas 401 bukan redirect
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
