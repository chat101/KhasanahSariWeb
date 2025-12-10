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
        Schema::create('jurnal_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

        $table->foreignId('jurnal_header_id')
            ->constrained('jurnal_header')
            ->onDelete('cascade');

        $table->foreignId('coa_id')
            ->constrained('coa');

        $table->decimal('debet', 18, 2)->default(0);
        $table->decimal('kredit', 18, 2)->default(0);

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_detail');
    }
};
