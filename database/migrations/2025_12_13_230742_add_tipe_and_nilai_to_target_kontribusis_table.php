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
        Schema::table('target_kontribusis', function (Blueprint $table) {
            $table->string('tipe', 10)->default('PERSEN')->after('nama'); // PERSEN | RUPIAH
            $table->decimal('nilai', 14, 2)->default(0)->after('tipe');   // angka persen atau rupiah
            $table->dropColumn('persen'); // kalau kolom persen sudah ada dan mau diganti total
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_kontribusis', function (Blueprint $table) {
            $table->decimal('persen', 5, 2)->default(0);
            $table->dropColumn(['tipe','nilai']);
        });
    }
};
