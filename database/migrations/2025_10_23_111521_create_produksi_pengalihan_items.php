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
        Schema::create('produksi_pengalihan_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pengalihan_id')->constrained('produksi_pengalihan')->cascadeOnDelete();

            // opsional taut ke detail WO kalau suatu saat perlu
            $t->foreignId('detail_perintah_produksi_id')->nullable();

            // produk sumber & tujuan
            $t->foreignId('source_mproducts_id')->constrained('mproducts');
            $t->foreignId('target_mproducts_id')->constrained('mproducts');

            // hanya pcs
            $t->unsignedInteger('qty_pcs'); // REQUIRED

            $t->string('keterangan', 500)->nullable();
            $t->timestamps();

            $t->index(['source_mproducts_id','created_at']);
            $t->index(['target_mproducts_id','created_at']);
          });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_pengalihan_items');
    }
};
