<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bantuan_gizi', function (Blueprint $table) {
            $table->id('bantuan_id');
            $table->foreignId('lansia_id')->constrained('lansia', 'lansia_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->decimal('skor_ranking', 8, 4)->nullable();
            $table->enum('status_penerima', ['penerima', 'tidak_penerima', 'pending'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['lansia_id', 'periode_bulan', 'periode_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bantuan_gizi');
    }
};
