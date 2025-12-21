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
            $table->tinyInteger('pakai_rule_produksi')->default(0)->after('aktif');

            // nilai khusus bila pakai rule produksi (nullable)
            $table->decimal('nilai_produksi_sendiri', 14, 2)->nullable()->after('nilai');
            $table->decimal('nilai_non_produksi_sendiri', 14, 2)->nullable()->after('nilai_produksi_sendiri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_kontribusis', function (Blueprint $table) {
            $table->dropColumn([
                'pakai_rule_produksi',
                'nilai_produksi_sendiri',
                'nilai_non_produksi_sendiri',
            ]);
        });
    }
};
