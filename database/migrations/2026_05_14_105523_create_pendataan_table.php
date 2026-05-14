<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendataan', function (Blueprint $table) {
            $table->id('pendataan_id');
            $table->foreignId('lansia_id')->constrained('lansia', 'lansia_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status_verifikasi', ['menunggu', 'terverifikasi', 'ditolak'])->default('menunggu');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->date('tanggal_input');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendataan');
    }
};
