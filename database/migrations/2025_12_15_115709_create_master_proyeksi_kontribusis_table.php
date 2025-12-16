<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_proyeksi_kontribusis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('toko_id')
                ->constrained('tokos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('jenis', 50)->default('ROTI'); // bebas: ROTI/GAS/TELUR/dll
            $table->date('tanggal');

            $table->unsignedInteger('qty')->default(0);
            $table->unsignedBigInteger('rupiah')->default(0);

            // bantu untuk reporting cepat
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');

            $table->timestamps();

            // 1 toko + 1 jenis + 1 tanggal = 1 record proyeksi (biar upload ulang aman)
            $table->unique(['toko_id', 'jenis', 'tanggal'], 'uniq_proyeksi_toko_jenis_tgl');
            $table->index(['periode_tahun', 'periode_bulan'], 'idx_proyeksi_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_proyeksi_kontribusis');
    }
};
