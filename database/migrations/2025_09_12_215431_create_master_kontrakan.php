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
        Schema::create('kontrakan_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('tokos')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('nilai_sewa');
            $table->date('mulai_sewa');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kontrakan');
    }
};
