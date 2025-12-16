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
        Schema::create('target_kontribusis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();     // DISC_MANUAL, RETUR, GAS, TELUR
            $table->string('nama');               // Disc Manual, Retur, dll
            $table->decimal('persen', 5, 2);      // contoh: 2.50 (%)
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_kontribusis');
    }
};
