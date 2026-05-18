<?php

/**
 * routes/web.php- Cara pemakaian middleware di routing.
 *
 * Middleware diterapkan berlapis:
 * 1. 'auth.custom' → cek sudah login
 * 2. 'role.admin'  → cek role admin   (khusus route /admin)
 *    'role.siswa'  → cek role siswa   (khusus route /siswa)
 *
 * Middleware dijalankan dari kiri ke kanan sesuai urutan array.
 * Jadi auth.custom selalu dijalankan dulu sebelum role check.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Siswa;
use PHPUnit\Framework\MockObject\Rule\Parameters;

// ─── Halaman Login (tidak butuh auth) ────────────────────────────────────────
Route::get('/', [LoginController::class, 'showForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Area Admin ───────────────────────────────────────────────────────────────
// Semua route di bawah ini dilindungi dua middleware:
// auth.custom → cek login, role.admin → cek role admin
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth.custom', 'role.admin'])
    ->group(function () {

        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/profile/password', [Admin\DashboardController::class, 'updatePassword'])
            ->name('profile.password');

        // Master Data- Akun Siswa
        Route::resource('akun-siswa', Admin\AkunSiswaController::class)
            ->except(['show'])
            ->parameters(['akun-siswa' => 'siswa']);
        Route::post('akun-siswa/bulk-pindah-kelas', [Admin\AkunSiswaController::class, 'bulkPindahKelas'])
            ->name('akun-siswa.bulk-pindah');

        // Master Data- Kelas
        Route::resource('kelas', Admin\KelasController::class)->except(['show'])
            ->except(['show'])
            ->parameters(['kelas' => 'kelas']);
        Route::patch('kelas/{kelas}/toggle-status',     [Admin\KelasController::class, 'toggleStatus'])->name('kelas.toggle-status');

        // Master Data- Pembina
        Route::resource('pembina', Admin\PembinaController::class)->except(['show']);
        Route::patch('pembina/{pembina}/toggle-status', [Admin\PembinaController::class, 'toggleStatus'])
            ->name('pembina.toggle-status');

        // Master Data- Kategori Ekskul
        Route::resource('kategori-ekskul', Admin\KategoriEkskulController::class)
            ->except(['show'])
            ->parameters(['kategori-ekskul' => 'kategoriEkskul']);
        Route::patch('kategori-ekskul/{kategoriEkskul}/toggle-status',
            [Admin\KategoriEkskulController::class, 'toggleStatus'])
            ->name('kategori-ekskul.toggle-status');

        // Master Data- Tahun Ajaran
        Route::resource('tahun-ajaran', Admin\TahunAjaranController::class)->except(['show', 'destroy', 'edit']);

        // Master Data- Kriteria (hanya edit, tidak bisa tambah/hapus)
        Route::resource('kriteria', Admin\KriteriaController::class)->only(['index', 'edit', 'update'])
        ->only(['index', 'edit', 'update'])
        ->parameters(['kriteria' => 'kriteria']);
        Route::patch('kriteria/{kriteria}/toggle-status',[Admin\KriteriaController::class, 'toggleStatus'])->name('kriteria.toggle-status');

        // Master Data- Ekskul
        Route::resource('ekskul', Admin\EkskulController::class)->except(['show']);
        Route::patch('ekskul/{ekskul}/toggle-status', [Admin\EkskulController::class, 'toggleStatus'])
            ->name('ekskul.toggle-status');

        // Master Data- Soal Rekomendasi
        Route::resource('soal', Admin\SoalController::class)->except(['show']);
        Route::patch('soal/{soal}/toggle-status', [Admin\SoalController::class, 'toggleStatus'])
            ->name('soal.toggle-status');

        // Pendaftaran- Set Timeline
        Route::get('pendaftaran', [Admin\PendaftaranAdminController::class, 'index'])
            ->name('pendaftaran.index');
        Route::post('pendaftaran/simpan', [Admin\PendaftaranAdminController::class, 'simpanTimeline'])
            ->name('pendaftaran.simpan');

        // Pendaftaran- Hasil Pendaftaran
        Route::get('pendaftaran/hasil', [Admin\PendaftaranAdminController::class, 'hasil'])
            ->name('pendaftaran.hasil');

            // Laporan Absensi
        Route::get('laporan-absensi', [Admin\LaporanAbsensiController::class, 'index'])
            ->name('laporan-absensi.index');
        Route::post('laporan-absensi/finalisasi', [Admin\LaporanAbsensiController::class, 'finalisasi'])
            ->name('laporan-absensi.finalisasi');
        Route::get('laporan-absensi/download', [Admin\LaporanAbsensiController::class, 'download'])
            ->name('laporan-absensi.download');
        Route::get('laporan-absensi/preview', [Admin\LaporanAbsensiController::class, 'preview'])
            ->name('laporan-absensi.preview');

        // Laporan Final
        Route::get('laporan-final', [Admin\LaporanFinalController::class, 'index'])
            ->name('laporan-final.index');
        Route::post('laporan-final/finalisasi', [Admin\LaporanFinalController::class, 'finalisasi'])
            ->name('laporan-final.finalisasi');
        Route::get('laporan-final/download', [Admin\LaporanFinalController::class, 'download'])
            ->name('laporan-final.download');
    });

// ─── Area Siswa ───────────────────────────────────────────────────────────────
// Semua route di bawah ini dilindungi dua middleware:
// auth.custom → cek login, role.siswa → cek role siswa + is_active
Route::prefix('siswa')
    ->name('siswa.')
    ->middleware(['auth.custom', 'role.siswa'])
    ->group(function () {

        Route::get('/dashboard', [Siswa\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/profile/password', [Siswa\DashboardController::class, 'updatePassword'])
            ->name('profile.password');

        // Informasi Ekskul (katalog)
        Route::get('informasi-ekskul', [Siswa\InformasiEkskulController::class, 'index'])
            ->name('informasi-ekskul.index');

        // Detail ekskul via AJAX untuk modal (mengembalikan JSON)
        Route::get('informasi-ekskul/{ekskul}/detail', [Siswa\InformasiEkskulController::class, 'detail'])
            ->name('informasi-ekskul.detail');

        // Tes Rekomendasi
        Route::get('tes-rekomendasi', [Siswa\TesRekomendasiController::class, 'index'])
            ->name('tes.index');
        Route::get('tes-rekomendasi/mulai', [Siswa\TesRekomendasiController::class, 'mulai'])
            ->name('tes.mulai');
        Route::post('tes-rekomendasi/submit', [Siswa\TesRekomendasiController::class, 'submit'])
            ->name('tes.submit');
        Route::get('tes-rekomendasi/hasil', [Siswa\TesRekomendasiController::class, 'hasil'])
            ->name('tes.hasil');
        // Hapus tes lama supaya bisa tes ulang (minta konfirmasi di frontend dulu)
        Route::delete('tes-rekomendasi/reset', [Siswa\TesRekomendasiController::class, 'reset'])
            ->name('tes.reset');

        // Pendaftaran Ekskul
        Route::get('pendaftaran', [Siswa\PendaftaranController::class, 'index'])
            ->name('pendaftaran.index');
        Route::post('pendaftaran/simpan', [Siswa\PendaftaranController::class, 'simpan'])
            ->name('pendaftaran.simpan');

        // Pengumuman & Pemilihan Ulang
        Route::get('pengumuman', [Siswa\PengumumanController::class, 'index'])
            ->name('pengumuman.index');
        Route::post('pengumuman/simpan-final', [Siswa\PengumumanController::class, 'simpanFinal'])
            ->name('pengumuman.simpan-final');
    });
