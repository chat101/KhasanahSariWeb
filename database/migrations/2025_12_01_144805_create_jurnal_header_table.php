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
        Schema::create('jurnal_header', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('no_bukti')->nullable(); // ← tambahan ini

            $table->date('tanggal');
            $table->string('keterangan')->nullable();

            // referensi transaksi (opsional)
            $table->string('ref_type')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_header');
    }
};
