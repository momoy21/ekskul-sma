<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\PeriodePendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdatePasswordRequest;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard admin dengan ringkasan data.
     */
    public function index()
    {
        $tahunAktif = TahunAjaran::aktif()->first();

        // Ambil periode aktif berdasarkan semester tahun ajaran yang berjalan
        $periodeAktif = $tahunAktif
            ? PeriodePendaftaran::where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
                ->where('semester', $tahunAktif->semester)
                ->first()
            : null;

        // Statistik ringkasan untuk card di dashboard
        $stats = [
            'total_siswa_aktif' => Siswa::where('status', 'aktif')->count(),
            'total_ekskul_aktif' => Ekskul::where('is_active', 1)->count(),
            'total_pendaftar'   => $periodeAktif
                ? DB::table('pendaftaran_siswa')
                    ->where('periode_id', $periodeAktif->periode_id)
                    ->where('status', '!=', 'draft')
                    ->count()
                : 0,
        ];

        return view('admin.dashboard', compact('tahunAktif', 'periodeAktif', 'stats'));
    }

    /**
     * Update password admin.
     * Tidak perlu password lama- langsung ganti.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $data = $request->validated();

        // Update password pengguna yang sedang login
        \App\Models\Pengguna::where('pengguna_id', session('pengguna_id'))
            ->update(['password' => Hash::make($data['password_baru'])]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
