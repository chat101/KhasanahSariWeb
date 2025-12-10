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
        Schema::create('journal_transaction_types', function (Blueprint $table) {
            $table->id();
        $table->string('code')->unique(); // biaya, pendapatan, mutasi_kas
        $table->string('nama');          // label tampilan
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_transaction_types');
    }
};
