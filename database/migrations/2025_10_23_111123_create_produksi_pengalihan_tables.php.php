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
        Schema::create('produksi_pengalihan', function (Blueprint $t) {
            $t->id();
            // sesuaikan nama tabel WO kamu
            $t->foreignId('perintah_produksi_id')->nullable();
            $t->date('tanggal')->index();
            $t->unsignedBigInteger('divisi_id')->nullable()->index();
            $t->text('catatan')->nullable();
            $t->timestamps();
          });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
