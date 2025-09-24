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
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            // gunakan tokos (plural) dan pastikan unsigned
            $table->foreignId('toko_id')->constrained('tokos')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kategori', 100)->index();   // <— ganti TEXT → VARCHAR + index
            $table->integer('qty')->nullable();
            $table->decimal('total_piutang', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piutang');
    }
};
