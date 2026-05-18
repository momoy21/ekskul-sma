<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminMiddleware- memastikan pengguna yang sedang login adalah admin.
 *
 * Dipasang setelah AuthMiddleware di semua route prefix /admin.
 * Jika role bukan admin, redirect ke login- bukan ke dashboard siswa,
 * supaya tidak membocorkan bahwa halaman admin ada.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            // Flush session supaya tidak ada state yang menggantung
            session()->flush();

            return redirect()
                ->route('login')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
