<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Pengguna;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * SiswaSeeder- membuat 11 akun siswa dummy untuk testing.
 *
 * Pola NISN & Password:
 * - NISN: 10 digit angka sama (0000000000, 1111111111, ..., 9999999999, 1111111111)
 * - Password: DDMM2000 format (dari pola digit)
 *
 * Contoh:
 * - Dummy1: NISN 0000000000, password 01012000
 * - Dummy2: NISN 1111111111, password 01012000
 * - Dummy10: NISN 9999999999, password 09092000
 * - Dummy11: NISN 1111111111, password 01012000
 */
class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil kelas pertama (asumsi sudah ada dari KelasSeeder)
        $kelas = Kelas::first();
        if (!$kelas) {
            $this->command->warn('Tidak ada kelas di database. Jalankan KelasSeeder terlebih dahulu.');
            return;
        }

        // Data 11 siswa dummy
        $dummyStudents = [
            // Dummy1-Dummy10: digit 0-9
            ['nama' => 'Dummy1', 'nisn' => '0000000000', 'password' => '01012000', 'digit' => 0],
            ['nama' => 'Dummy2', 'nisn' => '1111111111', 'password' => '01012000', 'digit' => 1],
            ['nama' => 'Dummy3', 'nisn' => '2222222222', 'password' => '02022000', 'digit' => 2],
            ['nama' => 'Dummy4', 'nisn' => '3333333333', 'password' => '03032000', 'digit' => 3],
            ['nama' => 'Dummy5', 'nisn' => '4444444444', 'password' => '04042000', 'digit' => 4],
            ['nama' => 'Dummy6', 'nisn' => '5555555555', 'password' => '05052000', 'digit' => 5],
            ['nama' => 'Dummy7', 'nisn' => '6666666666', 'password' => '06062000', 'digit' => 6],
            ['nama' => 'Dummy8', 'nisn' => '7777777777', 'password' => '07072000', 'digit' => 7],
            ['nama' => 'Dummy9', 'nisn' => '8888888888', 'password' => '08082000', 'digit' => 8],
            ['nama' => 'Dummy10', 'nisn' => '9999999999', 'password' => '09092000', 'digit' => 9],
            // Dummy11: kembali ke digit 1
            ['nama' => 'Dummy11', 'nisn' => '1111111111', 'password' => '01012000', 'digit' => 1],
        ];

        foreach ($dummyStudents as $data) {
            // Cek apakah siswa sudah ada
            if (Siswa::where('nisn', $data['nisn'])->exists()) {
                $this->command->info("Siswa NISN {$data['nisn']} sudah ada, skip.");
                continue;
            }

            // Convert password ke format date DDMM2000 untuk tanggal lahir
            // Password format: DDMM2000
            $day = substr($data['password'], 0, 2);
            $month = substr($data['password'], 2, 2);
            $year = substr($data['password'], 4, 4);

            // Handle invalid dates (00/00 → 01/01)
            $day = $day === '00' ? '01' : $day;
            $month = $month === '00' ? '01' : $month;

            // Buat tanggal lahir
            try {
                $tanggalLahir = \Carbon\Carbon::createFromFormat('dmY', $day . $month . $year);
            } catch (\Exception $e) {
                $this->command->warn("Invalid date for {$data['nama']}, using 2000-01-01");
                $tanggalLahir = \Carbon\Carbon::parse('2000-01-01');
            }

            // Buat akun Pengguna (login)
            $pengguna = Pengguna::create([
                'username'  => strtolower($data['nama']),
                'password'  => Hash::make($data['password']),
                'role'      => 'siswa',
                'is_active' => true,
            ]);

            // Buat profil Siswa
            Siswa::create([
                'pengguna_id'    => $pengguna->pengguna_id,
                'nisn'           => $data['nisn'],
                'nama_lengkap'   => $data['nama'],
                'tanggal_lahir'  => $tanggalLahir,
                'jenis_kelamin'  => $data['digit'] % 2 === 0 ? 'L' : 'P', // Alternating gender
                'kelas_id'       => $kelas->kelas_id,
                'status'         => 'aktif',
            ]);

            $this->command->info("✓ Created: {$data['nama']} (NISN: {$data['nisn']}, Password: {$data['password']})");
        }

        $this->command->info("\n berhasil dibuat");
    }
}
