<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel ekskul_pembina- pivot many-to-many antara ekskul dan pembina.
 * Satu ekskul bisa punya lebih dari satu pembina (misal: Karate punya 2).
 * Satu pembina juga bisa mengampu lebih dari satu ekskul (misal: Mr Riki).
 * Tidak pakai timestamps karena ini murni pivot tanpa data tambahan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul_pembina', function (Blueprint $table) {
            $table->unsignedBigInteger('ekskul_id');
            $table->unsignedBigInteger('pembina_id');

            // Composite primary key- satu ekskul tidak boleh punya pembina yang sama dua kali
            $table->primary(['ekskul_id', 'pembina_id']);

            $table->foreign('ekskul_id')
                ->references('ekskul_id')->on('ekskul')
                ->onDelete('cascade');

            $table->foreign('pembina_id')
                ->references('pembina_id')->on('pembina')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_pembina');
    }
};
