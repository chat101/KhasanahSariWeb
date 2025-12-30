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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories_transactions')->nullOnDelete();
            $table->date('tanggal');
            $table->enum('tipe', ['debit', 'kredit']);
            $table->decimal('jumlah', 15, 2);
            $table->string('ref_no')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('bukti')->nullable(); // path upload
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transaction');
    }
};
