<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\HasilRekomendasi;
use App\Models\JawabanTes;
use App\Models\Kriteria;
use App\Models\PeriodePendaftaran;
use App\Models\SoalRekomendasi;
use App\Models\TahunAjaran;
use App\Models\TesRekomendasi;
use App\Services\SawService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// tambahkan ini
use App\Http\Requests\SubmitTesRekomendasiRequest;

class TesRekomendasiController extends Controller
{
    /**
     * Halaman landing tes- menampilkan info tes dan tombol mulai.
     * Jika sudah pernah tes, tampilkan hasil + opsi tes ulang.
     */
    public function index()
    {
        [$periode, $tes] = $this->getPeriodeAndTes();

        return view('siswa.tes-rekomendasi.index', compact('periode', 'tes'));
    }

    /**
     * Halaman form tes- Tahap 1 (bobot) dan Tahap 2 (soal) dalam satu halaman
     * yang dibagi via stepper JS. Data soal dan kriteria sudah di-load di sini.
     */
    public function mulai()
    {
        [$periode, $tes] = $this->getPeriodeAndTes();

        // Kalau belum ada periode aktif, redirect balik
        if (! $periode) {
            return redirect()->route('siswa.tes.index')
                ->with('error', 'Belum ada periode pendaftaran yang aktif.');
        }

        // Ambil soal aktif dikelompokkan per kriteria untuk tampilan stepper
        $soalPerKriteria = SoalRekomendasi::with('kriteria')
            ->aktif()
            ->get()
            ->groupBy('kriteria_id');

        $kriteriaList = Kriteria::aktif()->get();

        // Jika sudah pernah tes, ambil jawaban lama untuk pre-fill form
        $jawabanLama = [];
        $bobotLama   = [];
        if ($tes && $tes->sudah_submit) {
            $jawabanLama = $tes->jawabanTes->pluck('nilai_jawaban', 'soal_id')->toArray();
            $bobotLama   = [
                'C1' => $tes->bobot_c1,
                'C2' => $tes->bobot_c2,
                'C3' => $tes->bobot_c3,
                'C4' => $tes->bobot_c4,
                'C5' => $tes->bobot_c5,
            ];
        }

        return view('siswa.tes-rekomendasi.mulai', compact(
            'periode', 'tes', 'soalPerKriteria', 'kriteriaList', 'jawabanLama', 'bobotLama'
        ));
    }

    /**
     * Proses submit tes.
     * Simpan bobot C1-C5, semua jawaban soal, lalu hitung SAW dan simpan hasilnya.
     */
    public function submit(SubmitTesRekomendasiRequest $request, SawService $sawService) // ✅ pakai Form Request
    {
        [$periode, $tesSudahAda] = $this->getPeriodeAndTes();

        if (! $periode) {
            return redirect()->route('siswa.tes.index')
                ->with('error', 'Tidak ada periode aktif saat ini.');
        }

        // Ambil semua soal aktif (dipakai untuk insert jawaban)
        $soalAktif = SoalRekomendasi::aktif()->pluck('soal_id')->toArray();

        DB::transaction(function () use ($request, $periode, $tesSudahAda, $soalAktif, $sawService) {
            $siswaId = session('siswa_id');

            // Hapus tes lama jika tes ulang (reset dulu)
            if ($tesSudahAda) {
                HasilRekomendasi::where('tes_id', $tesSudahAda->tes_id)->delete();
                JawabanTes::where('tes_id', $tesSudahAda->tes_id)->delete();
                $tesSudahAda->delete();
            }

            // Buat record tes baru
            $tes = TesRekomendasi::create([
                'siswa_id'   => $siswaId,
                'periode_id' => $periode->periode_id,
                'bobot_c1'   => $request->bobot_c1,
                'bobot_c2'   => $request->bobot_c2,
                'bobot_c3'   => $request->bobot_c3,
                'bobot_c4'   => $request->bobot_c4,
                'bobot_c5'   => $request->bobot_c5,
                'submitted_at' => now(),
            ]);

            // Simpan semua jawaban soal
            $jawabanInsert = [];
            foreach ($soalAktif as $soalId) {
                $jawabanInsert[] = [
                    'tes_id'        => $tes->tes_id,
                    'soal_id'       => $soalId,
                    'nilai_jawaban' => $request->input("jawaban.{$soalId}"),
                ];
            }
            JawabanTes::insert($jawabanInsert);

            // Hitung SAW- load jawaban dulu supaya relasi tersedia
            $tes->load('jawabanTes');
            $skorSaw = $sawService->hitung($tes);

            // Simpan top 3 hasil rekomendasi
            $hasilInsert = [];
            foreach (array_slice($skorSaw, 0, 3) as $index => $item) {
                $hasilInsert[] = [
                    'tes_id'     => $tes->tes_id,
                    'ekskul_id'  => $item['ekskul_id'],
                    'peringkat'  => $index + 1,
                    'skor_saw'   => $item['skor'],
                    'created_at' => now(),
                ];
            }
            HasilRekomendasi::insert($hasilInsert);
        });

        return redirect()->route('siswa.tes.hasil')
            ->with('success', 'Tes selesai! Berikut rekomendasi ekskul untuk kamu.');
    }

    /**
     * Halaman hasil rekomendasi top 3.
     */
    public function hasil()
    {
        [$periode, $tes] = $this->getPeriodeAndTes();

        if (! $tes || ! $tes->sudah_submit) {
            return redirect()->route('siswa.tes.index')
                ->with('error', 'Kamu belum mengikuti tes rekomendasi.');
        }

        $tes->load(['hasilRekomendasi.ekskul.pembina', 'hasilRekomendasi.ekskul.kategori']);

        return view('siswa.tes-rekomendasi.hasil', compact('tes', 'periode'));
    }

    /**
     * Reset tes- hapus tes dan hasil lama supaya siswa bisa tes ulang.
     * Dikonfirmasi via modal di frontend sebelum request ini dikirim.
     */
    public function reset()
    {
        [$periode, $tes] = $this->getPeriodeAndTes();

        if (! $tes) {
            return redirect()->route('siswa.tes.index');
        }

        DB::transaction(function () use ($tes) {
            HasilRekomendasi::where('tes_id', $tes->tes_id)->delete();
            JawabanTes::where('tes_id', $tes->tes_id)->delete();
            $tes->delete();
        });

        return redirect()->route('siswa.tes.mulai')
            ->with('success', 'Tes sebelumnya dihapus. Silakan isi ulang.');
    }

    /**
     * Helper: ambil periode dan tes aktif milik siswa yang sedang login.
     * Dipakai di hampir semua method di controller ini.
     *
     * @return array [PeriodePendaftaran|null, TesRekomendasi|null]
     */
    private function getPeriodeAndTes(): array
    {
        $tahunAktif = TahunAjaran::aktif()->first();

        if (! $tahunAktif) {
            return [null, null];
        }

        $periode = PeriodePendaftaran::where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
            ->where('semester', $tahunAktif->semester)
            ->first();

        $tes = $periode
            ? TesRekomendasi::with('hasilRekomendasi')
                ->where('siswa_id', session('siswa_id'))
                ->where('periode_id', $periode->periode_id)
                ->first()
            : null;

        return [$periode, $tes];
    }
}
