<?php

use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->operator = User::factory()->operator()->create();
    $this->lurah = User::factory()->lurah()->create();
});

test('admin can list pendataan', function () {
    Pendataan::factory(3)->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/pendataan')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('operator cannot access pendataan list', function () {
    $this->actingAs($this->operator)
        ->getJson('/api/v1/pendataan')
        ->assertForbidden();
});

test('admin can verify pendataan', function () {
    $pendataan = Pendataan::factory()->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/pendataan/{$pendataan->pendataan_id}/verifikasi", [
            'status_verifikasi' => 'terverifikasi',
        ])
        ->assertOk()
        ->assertJsonPath('data.status_verifikasi', 'terverifikasi');

    $this->assertDatabaseHas('pendataan', [
        'pendataan_id' => $pendataan->pendataan_id,
        'status_verifikasi' => 'terverifikasi',
        'verified_by' => $this->admin->id,
    ]);
});

test('admin can reject pendataan', function () {
    $pendataan = Pendataan::factory()->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/pendataan/{$pendataan->pendataan_id}/verifikasi", [
            'status_verifikasi' => 'ditolak',
        ])
        ->assertOk()
        ->assertJsonPath('data.status_verifikasi', 'ditolak');
});

test('verifikasi fails with invalid status', function () {
    $pendataan = Pendataan::factory()->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/pendataan/{$pendataan->pendataan_id}/verifikasi", [
            'status_verifikasi' => 'invalid',
        ])
        ->assertUnprocessable();
});
