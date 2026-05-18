<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\PeriodePendaftaran;
use App\Models\TahunAjaran;
use App\Services\ZonaSeleksiService;

class InformasiEkskulController extends Controller
{
    /**
     * Katalog informasi ekskul untuk siswa.
     * Ekskul diurutkan Senin → Selasa → Kamis → Jumat.
     * Status kuota (dots merah/kuning/hijau) dihitung dari jumlah pendaftar aktif.
     */
    public function index()
    {
        $tahunAktif = TahunAjaran::aktif()->first();
        $periode    = null;

        if ($tahunAktif) {
            $periode = PeriodePendaftaran::where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
                ->where('semester', $tahunAktif->semester)
                ->first();
        }

        // Ambil semua ekskul aktif dengan relasi yang dibutuhkan
        $ekskulList = Ekskul::with(['kategori', 'pembina'])
            ->aktif()
            ->urutHari()
            ->get();

        // Pakai perhitungan zona terpusat agar konsisten dengan halaman admin/pengumuman
        $zonaMap = $periode
            ? app(ZonaSeleksiService::class)->hitungZona($periode->periode_id)
            : [];

        // Tentukan warna dot setiap ekskul
        // merah < 9, kuning = 9, hijau >= 10
        $dotKuota = [];
        foreach ($ekskulList as $ekskul) {
            $data = $zonaMap[$ekskul->ekskul_id] ?? ['jumlah' => 0, 'zona' => 'merah'];

            $dotKuota[$ekskul->ekskul_id] = [
                'jumlah' => $data['jumlah'],
                'warna'  => $data['zona'],
            ];
        }

        return view('siswa.informasi-ekskul.index', compact(
            'ekskulList', 'dotKuota', 'tahunAktif', 'periode'
        ));
    }

    /**
     * Ambil detail satu ekskul untuk ditampilkan di modal.
     * Mengembalikan JSON karena dipanggil via AJAX saat klik kartu.
     */
    public function detail(Ekskul $ekskul)
    {
        $ekskul->load(['kategori', 'pembina']);

        // Pakai sumber hitung yang sama dengan halaman informasi dan admin
        $tahunAktif = TahunAjaran::aktif()->first();
        $jumlah     = 0;
        $warna      = 'merah';

        if ($tahunAktif) {
            $periode = PeriodePendaftaran::where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
                ->where('semester', $tahunAktif->semester)
                ->first();

            if ($periode) {
                $zonaMap = app(ZonaSeleksiService::class)->hitungZona($periode->periode_id);
                $data    = $zonaMap[$ekskul->ekskul_id] ?? ['jumlah' => 0, 'zona' => 'merah'];
                $jumlah  = $data['jumlah'];
                $warna   = $data['zona'];
            }
        }
        $kuota = $ekskul->kuota_minimal;

        return response()->json([
            'ekskul_id'           => $ekskul->ekskul_id,
            'nama_ekskul'         => $ekskul->nama_ekskul,
            'nama_pembina'        => $ekskul->nama_pembina,
            'nama_kategori'       => $ekskul->kategori->nama_kategori,
            'hari_pelaksanaan'    => $ekskul->hari_pelaksanaan,
            'lokasi'              => $ekskul->lokasi,
            'label_biaya'         => $ekskul->label_biaya,
            'label_fasilitas'     => $ekskul->label_fasilitas,
            'label_intensitas'    => $ekskul->label_intensitas,
            'deskripsi_kegiatan'  => $ekskul->deskripsi_kegiatan,
            'foto_url'            => $ekskul->foto_url,
            'kuota' => [
                'jumlah' => $jumlah,
                'minimal'=> $kuota,
                'warna'  => $warna,
            ],
        ]);
    }
}
