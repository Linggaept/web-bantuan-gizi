<?php

use App\Livewire\Admin\BantuanManagement;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view bantuan management page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/bantuan')->assertOk();
});

test('admin can set kuota', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(BantuanManagement::class)
        ->set('kuota', 25)
        ->set('periodeBulan', 5)
        ->set('periodeTahun', 2026)
        ->call('simpanKuota')
        ->assertHasNoErrors();

    expect(cache()->get('bantuan_kuota_5_2026')['kuota'])->toBe(25);
});

test('admin can trigger ranking', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    cache()->put('bantuan_kuota_5_2026', ['kuota' => 5, 'periode_bulan' => 5, 'periode_tahun' => 2026]);

    $lansia = Lansia::factory()->create(['created_by' => $operator->id, 'tanggal_lahir' => '1945-01-01']);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansia->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansia->lansia_id, 'hasil_periksa' => 'buruk']);

    Livewire::actingAs($admin)
        ->test(BantuanManagement::class)
        ->set('periodeBulan', 5)
        ->set('periodeTahun', 2026)
        ->call('jalankanRanking')
        ->assertSeeText('Ranking selesai');
});
