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
            $table->unsignedBigInteger('purchasing_id');
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 18, 2);
            $table->string('metode_bayar')->nullable();   // Kas Kecil / Kas Bank, dll
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('purchasing_id')
                  ->references('id')
                  ->on('purchasing')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_payments');
    }
};
