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
        Schema::create('loss_bahans', function (Blueprint $table) {
            $table->id();

            // tanggal transaksi loss
            $table->date('tanggal');

            // relasi toko internal (rekomendasi)
            $table->unsignedBigInteger('toko_id')->index();

            // opsional simpan id api (biar gampang match)
            $table->string('api_id', 64)->nullable()->index();

            $table->bigInteger('nominal')->default(0); // rupiah
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Sesuaikan nama tabel master toko kamu
            // kalau master toko: master_tokos atau master_toko, sesuaikan ya
            $table->foreign('toko_id')->references('id')->on('tokos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_bahans');
    }
};
