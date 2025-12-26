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
        Schema::table('kontribusi_harian_job_rows', function (Blueprint $table) {
            // untuk filter tanggal + jenis
            $table->index(['tanggal', 'jenis'], 'idx_khjr_filter');

            // untuk dedup (job, tanggal, jenis, ambil terbaru)
            $table->index(['job_id', 'tanggal', 'jenis', 'id'], 'idx_khjr_dedup');
        });

        Schema::table('kontribusi_harian_jobs', function (Blueprint $table) {
            $table->index(['status', 'toko_id'], 'idx_khj_status_toko');
        });

        Schema::table('loss_bahans', function (Blueprint $table) {
            $table->index(['toko_id', 'tanggal'], 'idx_loss_toko_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontribusi_harian_job_rows', function (Blueprint $table) {
            $table->dropIndex('idx_khjr_filter');
            $table->dropIndex('idx_khjr_dedup');
        });

        Schema::table('kontribusi_harian_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_khj_status_toko');
        });

        Schema::table('loss_bahans', function (Blueprint $table) {
            $table->dropIndex('idx_loss_toko_tanggal');
        });
    }
};
