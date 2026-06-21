<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bantuan_gizi', function (Blueprint $table) {
            $table->string('status_penerima')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bantuan_gizi', function (Blueprint $table) {
            $table->enum('status_penerima', ['penerima', 'tidak_penerima', 'pending'])->default('pending')->change();
        });
    }
};
