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
       Schema::table('budget_biaya_bulanans', function (Blueprint $table) {
            $table->string('deskripsi')->nullable()->after('idakun_api');

            // optional tapi sangat disarankan: unique per toko+akun+deskripsi+bulan
            $table->unique(['toko_id','idakun_api','deskripsi','tahun','bulan'], 'bbbul_unique_desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_biaya_bulanans', function (Blueprint $table) {
            $table->dropUnique('bbbul_unique_desc');
            $table->dropColumn('deskripsi');
        });
    }
};
