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
        Schema::create('budget_biaya', function (Blueprint $table) {
            $table->id();

    // Budget & realisasi PER TOKO + PER AKUN
    $table->unsignedBigInteger('toko_id');     // toko lokal
    $table->string('idakun_api', 50)->index(); // kode akun dari API (156, dst)

    // Info tambahan dari API (opsional)
    $table->string('tipe_api', 100)->nullable();
    $table->string('ket_api')->nullable();

    // Periode monitoring
    $table->date('start_date')->index();       // awal bulan
    $table->date('end_date')->index();         // tanggal berjalan / akhir bulan

    // INI YANG PENTING
    $table->double('budget', 15, 2)->default(0);   // budget akun untuk toko ini
    $table->double('realisasi', 15, 2)->default(0);// realisasi dari API untuk toko ini

    $table->timestamps();

    $table->foreign('toko_id')
        ->references('id')->on('tokos')
        ->onDelete('cascade');

    // tidak boleh ada duplikat budget utk toko+akun+periode yang sama
    $table->unique(
        ['toko_id', 'idakun_api', 'start_date', 'end_date'],
        'uniq_budget_toko_periode'
    );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_biaya');
    }
};
