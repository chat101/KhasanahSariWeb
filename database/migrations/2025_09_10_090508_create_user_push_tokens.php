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
        Schema::create('user_push_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('expo_token')->index();
            $t->string('native_token')->nullable();
            $t->string('device_brand')->nullable();
            $t->string('device_model')->nullable();
            $t->string('device_os')->nullable();
            $t->string('device_os_ver')->nullable();
            $t->boolean('is_emulator')->default(false);
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id','expo_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_push_tokens');
    }
};
