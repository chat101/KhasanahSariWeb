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
       Schema::table('budget_biaya', function (Blueprint $table) {
            $table->string('deskripsi')->nullable()->after('ket_api');

            // supaya 1 periode bisa beda deskripsi (tidak bentrok updateOrCreate)
            $table->index(['toko_id','idakun_api','deskripsi','start_date','end_date'], 'bb_idx_desc_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_biaya', function (Blueprint $table) {
            $table->dropIndex('bb_idx_desc_period');
            $table->dropColumn('deskripsi');
        });
    }
};
