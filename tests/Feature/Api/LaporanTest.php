<?php

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
    $this->operator = User::factory()->operator()->create();
});

test('admin can get laporan', function () {
    BantuanGizi::factory(5)->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('laporan supports filter by rw', function () {
    $lansiaRw1 = Lansia::factory()->create(['rw' => '01', 'created_by' => $this->operator->id]);
    $lansiaRw2 = Lansia::factory()->create(['rw' => '02', 'created_by' => $this->operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaRw1->lansia_id, 'status_penerima' => 'penerima']);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaRw2->lansia_id, 'status_penerima' => 'penerima']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan?rw=01');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

test('laporan supports filter by jenis penerima', function () {
    BantuanGizi::factory(2)->create(['status_penerima' => 'penerima']);
    BantuanGizi::factory(3)->create(['status_penerima' => 'tidak_penerima']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan?jenis=penerima');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

test('admin can download laporan as csv', function () {
    BantuanGizi::factory(3)->create(['status_penerima' => 'penerima']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan/download');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('operator cannot access laporan', function () {
    $this->actingAs($this->operator)
        ->getJson('/api/v1/laporan')
        ->assertForbidden();
});

test('lurah can access laporan', function () {
    $this->actingAs($this->lurah)
        ->getJson('/api/v1/laporan')
        ->assertOk();
});
