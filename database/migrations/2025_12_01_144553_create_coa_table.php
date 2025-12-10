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
        Schema::create('coa', function (Blueprint $table) {
            $table->bigIncrements('id');
        $table->string('kode', 50)->unique();
        $table->string('nama', 150);

        // tipe untuk laporan Neraca & Laba Rugi
        $table->enum('tipe', [
            'aset', 'kewajiban', 'modal', 'pendapatan', 'biaya'
        ]);

        // saldo normal → D / K
        $table->enum('normal_balance', ['D', 'K'])->nullable();

        // penanda akun kas (untuk dropdown Kas/Bank)
        $table->boolean('is_kas')->default(false);

        // penanda akun khusus → hutang_dagang, modal_pemilik, prive_pemilik, dll
        $table->string('default_role')->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
