<?php

use App\Livewire\Lurah\LaporanTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('lurah can view laporan page', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah/laporan')->assertOk();
});

test('laporan shows bantuan data', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['nama' => 'Pak Lurah Test', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($lurah)
        ->test(LaporanTable::class)
        ->assertSeeText('Pak Lurah Test');
});

test('laporan can filter by jenis', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $l1 = Lansia::factory()->create(['nama' => 'Si Penerima', 'created_by' => $operator->id]);
    $l2 = Lansia::factory()->create(['nama' => 'Si Bukan', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $l1->lansia_id, 'status_penerima' => 'penerima']);
    BantuanGizi::factory()->create(['lansia_id' => $l2->lansia_id, 'status_penerima' => 'tidak_penerima']);

    Livewire::actingAs($lurah)
        ->test(LaporanTable::class)
        ->set('filterJenis', 'penerima')
        ->assertSeeText('Si Penerima')
        ->assertDontSeeText('Si Bukan');
});

test('lurah can access print view', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah/laporan/print')->assertOk();
});
