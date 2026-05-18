<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pembina- daftar guru pembina ekskul.
 * Satu ekskul bisa punya lebih dari satu pembina (via pivot ekskul_pembina).
 * Pembina nonaktif tidak muncul saat create/edit ekskul.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembina', function (Blueprint $table) {
            $table->id('pembina_id');
            $table->string('nama_lengkap', 100);

            // 0 = tidak muncul di dropdown pilihan pembina saat isi/edit ekskul
            $table->tinyInteger('is_active')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembina');
    }
};
