<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BantuanController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LansiaController;
use App\Http\Controllers\Api\V1\LaporanController;
use App\Http\Controllers\Api\V1\PemeriksaanController;
use App\Http\Controllers\Api\V1\PendataanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::post('auth/login', [AuthController::class, 'login']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Operator + Admin: lansia CRUD
        Route::middleware('role:operator,admin')->group(function () {
            Route::apiResource('lansia', LansiaController::class, [
                'parameters' => ['lansia' => 'lansia'],
            ]);
            Route::post('lansia/{lansia}/foto-ktp', [LansiaController::class, 'uploadFotoKtp']);
            Route::apiResource('lansia.pemeriksaan', PemeriksaanController::class)->shallow();
        });

        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::get('pendataan', [PendataanController::class, 'index']);
            Route::post('pendataan/{pendataan}/verifikasi', [PendataanController::class, 'verifikasi']);
            Route::post('bantuan/ranking', [BantuanController::class, 'ranking']);
            Route::post('bantuan/kuota', [BantuanController::class, 'setKuota']);
        });

        // Admin + Lurah
        Route::middleware('role:admin,lurah')->group(function () {
            Route::get('bantuan', [BantuanController::class, 'index']);
            Route::get('bantuan/kuota', [BantuanController::class, 'getKuota']);
            Route::get('dashboard/stats', [DashboardController::class, 'stats']);
            Route::get('laporan', [LaporanController::class, 'index']);
            Route::get('laporan/download', [LaporanController::class, 'download']);
        });

        // Lurah only
        Route::middleware('role:lurah')->group(function () {
            Route::post('bantuan/{bantuan}/approve', [BantuanController::class, 'approve']);
        });

        // Lurah + Operator + Admin: read lansia
        Route::middleware('role:lurah,operator,admin')->group(function () {
            Route::get('lansia', [LansiaController::class, 'index']);
            Route::get('lansia/{lansia}', [LansiaController::class, 'show']);
            Route::get('lansia/{lansia}/status-bantuan', [LansiaController::class, 'statusBantuan']);
        });

    });
});
