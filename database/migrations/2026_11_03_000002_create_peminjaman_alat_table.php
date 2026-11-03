<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjaman_alat', function (Blueprint $table) {
            $table->id();
            $table->char('kode_peminjaman', 10)->unique();
            $table->string('nama_peminjam', 100);
            $table->string('nomor_identitas', 30)->unique();
            $table->unsignedTinyInteger('jumlah_alat');
            $table->unsignedBigInteger('alat_id');
            $table->timestamps();

            $table->foreign('alat_id')
                ->references('id')
                ->on('alat')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        // Pastikan CHECK constraint terpasang pada MariaDB/MySQL
        DB::statement('ALTER TABLE peminjaman_alat ADD CONSTRAINT chk_jumlah_alat CHECK (jumlah_alat BETWEEN 1 AND 10)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_alat');
    }
};
