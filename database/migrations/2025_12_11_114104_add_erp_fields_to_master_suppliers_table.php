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
        Schema::table('mssuppliers', function (Blueprint $table) {
            $table->unsignedInteger('tempo_hari')->default(0)->after('suppalamat'); // jatuh tempo dalam hari
            $table->decimal('max_hutang', 15, 2)->nullable()->after('tempo_hari');  // limit kredit
            $table->string('contact_person')->nullable()->after('max_hutang');
            $table->string('email')->nullable()->after('contact_person');
            $table->boolean('is_aktif')->default(true)->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mssuppliers', function (Blueprint $table) {
              $table->dropColumn([
                'tempo_hari',
                'max_hutang',
                'contact_person',
                'email',
                'is_aktif',
            ]);
        });
    }
};
