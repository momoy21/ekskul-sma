<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * LoginController- menangani login, logout, dan redirect berdasarkan role.
 *
 * Sistem autentikasi ini TIDAK menggunakan Laravel Auth bawaan (guard/provider).
 * Kita pakai session manual karena tabel pengguna punya struktur sendiri
 * dan tidak extend Authenticatable.
 */
class LoginController extends Controller
{
    /**
     * Tampilkan form login.
     * Jika pengguna sudah login, langsung redirect ke dashboard yang sesuai.
     */
    public function showForm()
    {
        if (session()->has('pengguna_id')) {
            return $this->redirectByRole(session('role'));
        }

        return view('auth.login');
    }

    /**
     * Proses login.
     *
     * Alur:
     * 1. Validasi input
     * 2. Cari pengguna by username
     * 3. Cek password (bcrypt)
     * 4. Cek is_active
     * 5. Simpan data ke session
     * 6. Redirect ke dashboard sesuai role
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:10',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username atau NISN wajib diisi.',
            'username.max'      => 'Username atau NISN maksimal 10 karakter.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $pengguna = Pengguna::where('username', $request->username)->first();

        // Username tidak ditemukan atau password salah- pesan error sama
        // supaya tidak membocorkan apakah username terdaftar atau tidak
        if (! $pengguna || ! Hash::check($request->password, $pengguna->password)) {
            return back()
                ->with('error', 'Username atau password yang kamu masukkan salah.')
                ->withInput(['username' => $request->username]);
        }

        // Akun dinonaktifkan (alumni atau diblokir admin)
        if (! $pengguna->is_active) {
            return back()
                ->with('error', 'Akun ini telah dinonaktifkan. Silakan hubungi guru koordinator.')
                ->withInput(['username' => $request->username]);
        }

        // Simpan data dasar ke session
        session([
            'pengguna_id' => $pengguna->pengguna_id,
            'username'    => $pengguna->username,
            'role'        => $pengguna->role,
            'is_active'   => (bool) $pengguna->is_active,
        ]);

        // Untuk siswa, tambahkan data profil ke session
        // supaya tidak perlu query ulang di setiap halaman
        if ($pengguna->role === 'siswa') {
            $siswa = $pengguna->siswa()->with('kelas')->first();

            session([
                'siswa_id'         => $siswa->siswa_id,
                'nama'             => $siswa->nama_lengkap,
                'nisn'             => $siswa->nisn,
                'label_kelas'      => $siswa->label_kelas,
                'jenis_kelamin'    => $siswa->jenis_kelamin,
            ]);
        }

        // Regenerate session ID setelah login untuk mencegah session fixation attack
        $request->session()->regenerate();

        return $this->redirectByRole($pengguna->role);
    }

    /**
     * Logout- hapus semua session dan redirect ke login.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Kamu berhasil logout.');
    }

    /**
     * Redirect ke dashboard sesuai role pengguna.
     */
    private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
