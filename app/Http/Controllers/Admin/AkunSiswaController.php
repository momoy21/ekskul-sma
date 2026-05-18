<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengguna;
use App\Models\Siswa;
use App\Services\ZonaSeleksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreSiswaRequest; // Validasi form di StoreSiswaRequest
use App\Http\Requests\UpdateSiswaRequest;
use App\Http\Requests\BulkPindahKelasRequest;

class AkunSiswaController extends Controller
{
    /**
     * Daftar siswa dengan filter kelas, tingkat, status, dan search nama/NISN.
     * Dibagi dua tab: Kelas Aktif dan Alumni.
     */
    public function index(Request $request)
    {
        $query = Siswa::with(['kelas', 'pengguna'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('nama_lengkap', 'like', "%{$request->search}%")
                          ->orWhere('nisn', 'like', "%{$request->search}%");
                });
            })
            ->when($request->tingkat, fn($q) =>
                $q->whereHas('kelas', fn($k) => $k->where('tingkat', $request->tingkat))
            )
            ->when($request->kelas_id, fn($q) =>
                $q->where('kelas_id', $request->kelas_id)
            );

        // Tab aktif/alumni - default tab aktif
        $tab = $request->get('tab', 'aktif');
        if ($tab === 'alumni') {
            $query->where('status', 'alumni');
        } else {
            $query->where('status', 'aktif');
        }

        $siswa    = $query->orderBy('nama_lengkap')->paginate(10)->withQueryString();
        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('admin.akun-siswa.index', compact('siswa', 'kelasList', 'tab'));
    }

    /**
     * Form tambah akun siswa baru.
     */
    public function create()
    {
        // Ambil kelas aktif saja, dikelompokkan per tingkat untuk dropdown
        $kelasList = Kelas::aktif()
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.akun-siswa.create', compact('kelasList'));
    }

    /**
     * Simpan akun siswa baru.
     * Membuat dua record sekaligus: pengguna (login) + siswa (profil).
     */
    public function store(StoreSiswaRequest $request)
    {
        $data = $request->validated();

        // Bungkus dalam transaction - kalau salah satu gagal, keduanya di-rollback
        DB::transaction(function () use ($data) {
            // Password default = tanggal lahir format DDMMYYYY
            $passwordDefault = \Carbon\Carbon::parse($data['tanggal_lahir'])->format('dmY');

            // 1. Buat akun login
            $pengguna = Pengguna::create([
                'username'  => $data['nisn'],
                'password'  => Hash::make($passwordDefault),
                'role'      => 'siswa',
                'is_active' => 1,
            ]);

            // 2. Buat profil siswa, terhubung ke akun yang baru dibuat
            Siswa::create([
                'pengguna_id'   => $pengguna->pengguna_id,
                'nisn'          => $data['nisn'],
                'nama_lengkap'  => $data['nama_lengkap'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'kelas_id'      => $data['kelas_id'],
                'status'        => 'aktif',
            ]);
        });

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', "Akun siswa {$data['nama_lengkap']} berhasil ditambahkan.");
    }

    /**
     * Form edit akun siswa- bisa ubah data profil dan password (opsional).
     */
    public function edit(Siswa $siswa)
    {
        $siswa->load(['kelas', 'pengguna']);

        $kelasList = Kelas::aktif()
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.akun-siswa.edit', compact('siswa', 'kelasList'));
    }

    /**
     * Update data siswa.
     * NISN tidak bisa diubah (sudah jadi username di pengguna).
     * Password hanya diubah jika field diisi.
     * Jika status berubah ke 'alumni', HARD DELETE pilihan ekskul & recalc zones.
     * Saat reactivation, akun dianggap fresh tanpa data lama (integritas data).
     */
    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        $data = $request->validated();
        $statusBerubahKeAlumni = ($siswa->status === 'aktif' && $data['status'] === 'alumni');

        DB::transaction(function () use ($data, $siswa, $statusBerubahKeAlumni) {
            // Update profil siswa
            $siswa->update([
                'nama_lengkap'  => $data['nama_lengkap'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'kelas_id'      => $data['kelas_id'],
                'status'        => $data['status'],
            ]);

            // Update password hanya jika diisi
            if (!empty($data['password_baru'])) {
                $siswa->pengguna->update([
                    'password' => Hash::make($data['password_baru']),
                ]);
            }

            // Jika status alumni, nonaktifkan akun login
            // Jika status aktif kembali, aktifkan akun login
            $siswa->pengguna->update([
                'is_active' => $data['status'] === 'aktif' ? 1 : 0,
            ]);

            // ✅ Jika status berubah ke alumni, HARD DELETE pilihan & recalc zones
            if ($statusBerubahKeAlumni) {
                // Ambil semua periode yang student terdaftar
                $periodeIds = $siswa->pendaftaran()
                    ->pluck('periode_id')
                    ->unique()
                    ->toArray();

                // HARD DELETE semua pilihan ekskul siswa ini (tidak bisa di-restore)
                $siswa->pendaftaran()
                    ->each(function ($pendaftaran) {
                        $pendaftaran->pilihanEkskul()->forceDelete();
                    });

                // HARD DELETE semua pendaftaran siswa di semua periode
                $siswa->pendaftaran()->forceDelete();

                // Recalculate zones untuk semua periode yang terkena dampak
                foreach ($periodeIds as $periodeId) {
                    app(ZonaSeleksiService::class)->updateStatusZona($periodeId);
                }
            }
        });

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', "Data siswa {$siswa->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Pindah kelas massal (bulk action).
     * Siswa yang dipilih via checkbox dipindahkan ke kelas tujuan sekaligus.
     */
    public function bulkPindahKelas(BulkPindahKelasRequest $request)
    {
        $data = $request->validated();

        $jumlah = Siswa::whereIn('siswa_id', $data['siswa_ids'])
            ->update(['kelas_id' => $data['kelas_tujuan_id']]);

        $kelas = Kelas::find($data['kelas_tujuan_id']);

        return back()->with('success', "{$jumlah} siswa berhasil dipindahkan ke kelas {$kelas->label}.");
    }
}
