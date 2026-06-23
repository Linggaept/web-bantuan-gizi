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
        Schema::table('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->unsignedTinyInteger('periode_bulan')->nullable()->after('catatan');
            $table->unsignedSmallInteger('periode_tahun')->nullable()->after('periode_bulan');
            $table->unique(['lansia_id', 'periode_bulan', 'periode_tahun'], 'uq_periksa_lansia_periode');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->dropUnique('uq_periksa_lansia_periode');
            $table->dropColumn(['periode_bulan', 'periode_tahun']);
        });
    }
};
