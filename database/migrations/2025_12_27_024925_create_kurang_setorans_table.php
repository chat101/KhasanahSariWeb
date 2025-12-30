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
        Schema::create('kurang_setorans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('toko_id');
            $table->date('tanggal');
            $table->bigInteger('nominal')->default(0)->comment('Nominal kurang setoran dalam Rp');
            $table->text('keterangan')->nullable()->comment('Catatan/alasan kurang setoran');
            $table->timestamps();

            // Foreign key
            $table->foreign('toko_id')
                  ->references('id')
                  ->on('tokos')
                  ->onDelete('cascade');

            // Index untuk query yang sering dijalankan
            $table->index(['toko_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurang_setorans');
    }
};
