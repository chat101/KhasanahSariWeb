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
        Schema::create('journal_transaction_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_type_id')
                ->constrained('journal_transaction_types')
                ->onDelete('cascade');

            $table->enum('side', ['debit', 'kredit']);

            // urutan baris jurnal
            $table->unsignedInteger('order_no')->default(1);

            /**
             * nilai source_key bisa:
             * - input_akun
             * - input_kas
             * - input_kas_asal
             * - input_kas_tujuan
             * - role:hutang_dagang
             * - role:modal_pemilik
             * - role:prive_pemilik
             */
            $table->string('source_key');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_transaction_templates');
    }
};
