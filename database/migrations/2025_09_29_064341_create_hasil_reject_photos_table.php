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
        Schema::create('hasil_reject_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hasil_reject_id')->constrained('hasil_reject')->cascadeOnDelete();
            $t->string('path', 512);
            $t->string('url', 1024)->nullable();
            $t->string('mime_type', 64)->nullable();
            $t->unsignedBigInteger('size_bytes')->nullable();
            $t->unsignedInteger('width')->nullable();
            $t->unsignedInteger('height')->nullable();
            $t->timestamp('taken_at')->nullable();
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['hasil_reject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_reject_photos');
    }
};
