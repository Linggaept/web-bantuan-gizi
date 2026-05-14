<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Livewire\Admin\BantuanManagement;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\LansiaForm;
use App\Livewire\Admin\LansiaTable;
use App\Livewire\Admin\LaporanTable;
use App\Livewire\Admin\MonitoringTable;
use App\Livewire\Lurah\ApprovalTable as LurahApproval;
use App\Livewire\Lurah\Dashboard as LurahDashboard;
use App\Livewire\Lurah\LaporanTable as LurahLaporan;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::prefix('dashboard')->middleware(['auth', 'web.role:admin'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/lansia', LansiaTable::class)->name('dashboard.lansia');
    Route::get('/lansia/{id}/edit', LansiaForm::class)->name('dashboard.lansia.edit');
    Route::get('/bantuan', BantuanManagement::class)->name('dashboard.bantuan');
    Route::get('/laporan', LaporanTable::class)->name('dashboard.laporan');
    Route::get('/laporan/print', [AdminAuthController::class, 'laporanPrint'])->name('dashboard.laporan.print');
    Route::get('/monitoring', MonitoringTable::class)->name('dashboard.monitoring');
});

Route::prefix('lurah')->middleware(['auth', 'web.role:lurah'])->group(function () {
    Route::get('/', LurahDashboard::class)->name('lurah.dashboard');
    Route::get('/approval', LurahApproval::class)->name('lurah.approval');
    Route::get('/laporan', LurahLaporan::class)->name('lurah.laporan');
    Route::get('/laporan/print', [AdminAuthController::class, 'laporanPrint'])->name('lurah.laporan.print');
});
