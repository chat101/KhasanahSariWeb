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
        Schema::create('master_trend_inflasis', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('tahun');      // contoh: 2025
            $table->unsignedTinyInteger('bulan');       // 1..12

            // Simpan angka persen apa adanya:
            // trend: -17 berarti -17%
            // inflasi: 1.2 berarti 1.2%
            $table->decimal('trend', 8, 2)->default(0);     // boleh negatif
            $table->decimal('inflasi', 8, 2)->default(0);   // umumnya positif

            $table->timestamps();

            $table->unique(['tahun', 'bulan']); // 1 tahun hanya 1 data per bulan
            $table->index(['tahun', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_trend_inflasis');
    }
};
