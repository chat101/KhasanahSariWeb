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
        Schema::table('loss_bahans', function (Blueprint $table) {
            // simpan relasi ke master msbarangs (nullable)
            $table->unsignedBigInteger('barang_id')->nullable()->index()->after('api_id');

            // qty barang yang hilang
            $table->integer('qty')->default(0)->after('barang_id');

            // foreign key ke tabel msbarangs jika ada
            $table->foreign('barang_id')->references('id')->on('msbarangs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_bahans', function (Blueprint $table) {
            // drop foreign key first, kemudian kolom
            $table->dropForeign(['barang_id']);
            $table->dropColumn(['barang_id', 'qty']);
        });
    }
};
