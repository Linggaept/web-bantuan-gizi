<?php

use App\Livewire\Admin\LaporanTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view laporan page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/laporan')->assertOk();
});

test('laporan shows bantuan data', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['nama' => 'Budi Laporan', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($admin)
        ->test(LaporanTable::class)
        ->assertSeeText('Budi Laporan');
});

test('laporan can filter by jenis penerima', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansiaPenerima = Lansia::factory()->create(['nama' => 'Si Penerima', 'created_by' => $operator->id]);
    $lansiaNoN = Lansia::factory()->create(['nama' => 'Si Bukan', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaPenerima->lansia_id, 'status_penerima' => 'penerima']);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaNoN->lansia_id, 'status_penerima' => 'tidak_penerima']);

    Livewire::actingAs($admin)
        ->test(LaporanTable::class)
        ->set('filterJenis', 'penerima')
        ->assertSeeText('Si Penerima')
        ->assertDontSeeText('Si Bukan');
});

test('admin can access print view', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/laporan/print')->assertOk();
});
