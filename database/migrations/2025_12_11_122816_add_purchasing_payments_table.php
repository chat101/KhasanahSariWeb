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
        Schema::create('purchasing_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchasing_id')->constrained('purchasing');
            $table->date('tgl_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('metode_bayar')->nullable();   // Kas / Bank / dll
            $table->string('no_bukti')->nullable();       // No bukti kas / bank
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
