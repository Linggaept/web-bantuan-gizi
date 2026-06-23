<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand enum to allow both old and new values before data migration
        DB::statement("ALTER TABLE pemeriksaan_kesehatan MODIFY COLUMN hasil_periksa ENUM('baik','sedang','buruk','sehat','sakit') NOT NULL");

        DB::table('pemeriksaan_kesehatan')
            ->where('hasil_periksa', 'baik')
            ->update(['hasil_periksa' => 'sehat']);

        DB::table('pemeriksaan_kesehatan')
            ->whereIn('hasil_periksa', ['sedang', 'buruk'])
            ->update(['hasil_periksa' => 'sakit']);

        // Narrow enum to final values only
        DB::statement("ALTER TABLE pemeriksaan_kesehatan MODIFY COLUMN hasil_periksa ENUM('sehat','sakit') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pemeriksaan_kesehatan MODIFY COLUMN hasil_periksa ENUM('baik','sedang','buruk') NOT NULL");

        DB::table('pemeriksaan_kesehatan')
            ->where('hasil_periksa', 'sehat')
            ->update(['hasil_periksa' => 'baik']);

        DB::table('pemeriksaan_kesehatan')
            ->where('hasil_periksa', 'sakit')
            ->update(['hasil_periksa' => 'sedang']);
    }
};
