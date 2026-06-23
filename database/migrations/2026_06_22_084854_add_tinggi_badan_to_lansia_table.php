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
        Schema::table('lansia', function (Blueprint $table) {
            $table->decimal('tinggi_badan', 5, 2)->nullable()->after('rw');
            $table->enum('kondisi_kesehatan', ['sehat', 'sakit'])->nullable()->after('tinggi_badan');
        });
    }

    public function down(): void
    {
        Schema::table('lansia', function (Blueprint $table) {
            $table->dropColumn(['tinggi_badan', 'kondisi_kesehatan']);
        });
    }
};
