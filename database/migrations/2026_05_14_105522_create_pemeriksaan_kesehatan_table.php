<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->id('pemeriksaan_id');
            $table->foreignId('lansia_id')->constrained('lansia', 'lansia_id')->cascadeOnDelete();
            $table->date('tanggal_periksa');
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->string('tekanan_darah', 20)->nullable();
            $table->enum('hasil_periksa', ['baik', 'sedang', 'buruk']);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_kesehatan');
    }
};
