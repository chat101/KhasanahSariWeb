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
        Schema::create('kontribusi_harian_job_rows', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('job_id');
      $table->date('tanggal');                 // tgl per baris
      $table->string('jenis', 30);             // BY TARGET / BY BULAN LALU

      $table->decimal('selisih_persen', 8, 2)->nullable();
      $table->bigInteger('selisih_rp')->default(0);
      $table->bigInteger('kontribusi_rp')->default(0);

      $table->decimal('disc_persen', 8, 2)->nullable();
      $table->bigInteger('disc_rp')->default(0);

      $table->decimal('retur_persen', 8, 2)->nullable();
      $table->bigInteger('retur_rp')->default(0);

      $table->decimal('gas_persen', 8, 2)->nullable();
      $table->bigInteger('gas_rp')->default(0);

      $table->decimal('telur_persen', 8, 2)->nullable();
      $table->bigInteger('telur_rp')->default(0);

      $table->bigInteger('loss_bahan')->default(0);
      $table->bigInteger('total_kontribusi')->default(0);

      $table->json('payload')->nullable();     // simpan row mentah kalau mau

      $table->timestamps();

      $table->foreign('job_id')->references('id')->on('kontribusi_harian_jobs')->onDelete('cascade');
      $table->index(['job_id','tanggal']);
      $table->index(['job_id','jenis']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('kontribusi_harian_job_rows');
    }
};
