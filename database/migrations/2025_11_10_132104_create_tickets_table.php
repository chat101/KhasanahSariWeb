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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // 🔹 Relasi user pelapor (nullable untuk keamanan)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // 🔹 Relasi ke tabel divisi (jika user punya divisi)
            $table->foreignId('divisi_id')
                ->nullable()
                ->constrained('divisi')
                ->onDelete('set null');

            // 🔹 Informasi inti tiket
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('photo_paths')->nullable(); // path di storage

            // 🔹 Kategori opsional (misal: listrik, software, perangkat)
            $table->string('category')->nullable();

            // 🔹 Status progress tiket
            $table->enum('status', [
                'pending',   // baru dibuat, menunggu teknisi
                'progress',  // sedang dikerjakan
                'done',      // selesai
                'cancelled'  // dibatalkan
            ])->default('pending');

            // 🔹 Waktu status berubah
            $table->timestamp('handled_at')->nullable();  // mulai ditangani
            $table->timestamp('closed_at')->nullable();   // selesai ditutup

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
