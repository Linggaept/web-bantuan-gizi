<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pemeriksaan_kesehatan MODIFY COLUMN hasil_periksa ENUM('baik','sedang','buruk','sehat','sakit') NOT NULL");
        }

        DB::table('pemeriksaan_kesehatan')
            ->where('hasil_periksa', 'baik')
            ->update(['hasil_periksa' => 'sehat']);

        DB::table('pemeriksaan_kesehatan')
            ->whereIn('hasil_periksa', ['sedang', 'buruk'])
            ->update(['hasil_periksa' => 'sakit']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pemeriksaan_kesehatan MODIFY COLUMN hasil_periksa ENUM('sehat','sakit') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pemeriksaan_kesehatan MODIFY COLUMN hasil_periksa ENUM('baik','sedang','buruk') NOT NULL");
        }

        DB::table('pemeriksaan_kesehatan')
            ->where('hasil_periksa', 'sehat')
            ->update(['hasil_periksa' => 'baik']);

        DB::table('pemeriksaan_kesehatan')
            ->where('hasil_periksa', 'sakit')
            ->update(['hasil_periksa' => 'sedang']);
    }
};
