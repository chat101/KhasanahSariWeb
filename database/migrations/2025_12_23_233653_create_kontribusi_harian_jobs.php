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
       Schema::create('kontribusi_harian_jobs', function (Blueprint $table) {
      $table->id();
      $table->date('tanggal_awal');
      $table->date('tanggal_akhir');
      $table->unsignedBigInteger('toko_id');
      $table->string('nama_toko')->nullable();

      $table->json('grand_totals')->nullable(); // simpan array grandTotals
      $table->string('status')->default('ok');  // ok / error
      $table->text('error')->nullable();

      $table->timestamps();

      $table->unique(['tanggal_awal','tanggal_akhir','toko_id'], 'uniq_kontribusi_job');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('kontribusi_harian_jobs');
    }
};
