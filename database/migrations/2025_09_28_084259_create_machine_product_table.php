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
        Schema::create('machine_product', function (Blueprint $table) {
            $table->id();

            // ⚠️ machines.id = BIGINT, mproducts.id = INT (lihat screenshot Anda)
            $table->unsignedInteger('machine_id');   // FK ke machines.id (BIGINT)
            $table->unsignedInteger('mproduct_id');     // FK ke mproducts.id (INT)

            // atribut khusus relasi (opsional, boleh null)
            $table->unsignedInteger('kapasitas_per_jam')->nullable();
            $table->unsignedSmallInteger('waktu_setup_menit')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // hindari duplikat pasangan
            $table->unique(['machine_id', 'mproduct_id']);

            // Foreign keys
            $table->foreign('machine_id')
                  ->references('id')->on('machines')
                  ->cascadeOnDelete();   // hapus relasi jika mesin dihapus

            $table->foreign('mproduct_id')
                  ->references('id')->on('mproducts')
                  ->restrictOnDelete();  // cegah hapus produk jika masih terhubung
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_product');
    }
};
