<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed akun admin (guru koordinator).
 * Username dan password bisa direset via halaman profile setelah login.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Pengguna::create([
            'username'  => 'admin',
            'password'  => Hash::make('admin123456789'),
            'role'      => 'admin',
            'is_active' => 1,
        ]);
    }
}
