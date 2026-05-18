<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\TahunAjaran;
use App\Models\PeriodePendaftaran;
use App\Models\TesRekomendasi;
use App\Models\PendaftaranSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdatePasswordRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $tahunAktif  = TahunAjaran::aktif()->first();
        $siswaId     = session('siswa_id');

        // Cek status tes dan pendaftaran di periode aktif
        $periode = $tahunAktif
            ? PeriodePendaftaran::where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
                ->where('semester', $tahunAktif->semester)
                ->first()
            : null;

        $sudahTes        = false;
        $sudahDaftar     = false;

        if ($periode) {
            $sudahTes    = TesRekomendasi::where('siswa_id', $siswaId)
                ->where('periode_id', $periode->periode_id)
                ->whereNotNull('submitted_at')
                ->exists();

            $sudahDaftar = PendaftaranSiswa::where('siswa_id', $siswaId)
                ->where('periode_id', $periode->periode_id)
                ->where('status', '!=', 'draft')
                ->exists();
        }

        return view('siswa.dashboard', compact('tahunAktif', 'periode', 'sudahTes', 'sudahDaftar'));
    }

    /** Update password siswa- tidak perlu password lama. */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $data = $request->validated();

        Pengguna::where('pengguna_id', session('pengguna_id'))
            ->update(['password' => Hash::make($data['password_baru'])]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
