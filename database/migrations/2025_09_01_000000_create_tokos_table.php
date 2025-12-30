<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tokos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('nmtoko');
            $table->string('api_id')->nullable();
            $table->string('api_name')->nullable();
            $table->string('alamat')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('produksi_sendiri')->default(false);
            $table->timestamps();
            // Note: intentionally not adding FK to area here to avoid ordering issues in tests
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokos');
    }
};
