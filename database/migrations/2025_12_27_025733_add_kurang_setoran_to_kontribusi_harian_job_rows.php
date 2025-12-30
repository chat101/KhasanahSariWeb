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
            $table->bigInteger('kurang_setoran')->default(0)->after('loss_bahan')
                  ->comment('Kurang setoran untuk hari tersebut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontribusi_harian_job_rows', function (Blueprint $table) {
            $table->dropColumn('kurang_setoran');
        });
    }
};
