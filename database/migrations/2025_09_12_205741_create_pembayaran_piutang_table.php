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
        Schema::create('pembayaran_piutang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piutang_id')->constrained('piutang')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('tgl_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('metode', 50)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('tgl_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_piutang');
    }
};
