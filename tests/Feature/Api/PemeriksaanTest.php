<?php

use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->operator = User::factory()->operator()->create();
    $this->lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);
});

test('operator can list pemeriksaan for a lansia', function () {
    PemeriksaanKesehatan::factory(3)->create(['lansia_id' => $this->lansia->lansia_id]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/lansia/{$this->lansia->lansia_id}/pemeriksaan")
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('operator can create pemeriksaan', function () {
    $payload = [
        'tanggal_periksa' => '2026-05-10',
        'berat_badan' => 55.5,
        'tekanan_darah' => '120/80',
        'hasil_periksa' => 'baik',
        'catatan' => 'Normal',
    ];

    $this->actingAs($this->operator)
        ->postJson("/api/v1/lansia/{$this->lansia->lansia_id}/pemeriksaan", $payload)
        ->assertCreated()
        ->assertJsonPath('data.hasil_periksa', 'baik');

    $this->assertDatabaseHas('pemeriksaan_kesehatan', [
        'lansia_id' => $this->lansia->lansia_id,
        'hasil_periksa' => 'baik',
    ]);
});

test('create pemeriksaan fails with invalid hasil_periksa', function () {
    $this->actingAs($this->operator)
        ->postJson("/api/v1/lansia/{$this->lansia->lansia_id}/pemeriksaan", [
            'tanggal_periksa' => '2026-05-10',
            'hasil_periksa' => 'invalid_value',
        ])
        ->assertUnprocessable();
});

test('operator can view single pemeriksaan', function () {
    $periksa = PemeriksaanKesehatan::factory()->create(['lansia_id' => $this->lansia->lansia_id]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/pemeriksaan/{$periksa->pemeriksaan_id}")
        ->assertOk()
        ->assertJsonPath('data.pemeriksaan_id', $periksa->pemeriksaan_id);
});

test('operator can update pemeriksaan', function () {
    $periksa = PemeriksaanKesehatan::factory()->create(['lansia_id' => $this->lansia->lansia_id]);

    $this->actingAs($this->operator)
        ->putJson("/api/v1/pemeriksaan/{$periksa->pemeriksaan_id}", ['hasil_periksa' => 'buruk'])
        ->assertOk()
        ->assertJsonPath('data.hasil_periksa', 'buruk');
});
