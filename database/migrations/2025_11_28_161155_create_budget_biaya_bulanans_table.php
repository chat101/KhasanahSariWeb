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
        Schema::create('budget_biaya_bulanans', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('toko_id');        // FK ke master_tokos
            $table->string('idakun_api', 50)->index();    // kode akun dari API (156, dst)

            $table->year('tahun');                        // contoh: 2025
            $table->unsignedTinyInteger('bulan');         // 1–12
            $table->string('jenis', 10)->default('rupiah'); // 'rupiah' atau 'persen'
            $table->bigInteger('senin')->nullable();
            $table->bigInteger('selasa')->nullable();
            $table->bigInteger('rabu')->nullable();
            $table->bigInteger('kamis')->nullable();
            $table->bigInteger('jumat')->nullable();
            $table->bigInteger('sabtu')->nullable();
            $table->bigInteger('minggu')->nullable();
            $table->double('budget', 15, 2)->default(0);  // budget bulanan

            $table->string('nama_akun')->nullable();      // opsional, bantu tampilan
            $table->string('tipe_api', 100)->nullable();  // TRANSPORTASI, dll

            $table->timestamps();

            $table->foreign('toko_id')
                ->references('id')->on('tokos') // sesuaikan nama tabelmu
                ->onDelete('cascade');

            $table->unique(
                ['toko_id', 'idakun_api', 'tahun', 'bulan'],
                'uniq_budget_toko_akun_bulan'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_biaya_bulanans');
    }
};
