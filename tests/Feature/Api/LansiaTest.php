<?php

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->operator = User::factory()->operator()->create();
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
});

test('operator can list lansia', function () {
    Lansia::factory(3)->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->getJson('/api/v1/lansia')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('list lansia supports filter by nama', function () {
    Lansia::factory()->create(['nama' => 'Budi Santoso', 'created_by' => $this->operator->id]);
    Lansia::factory()->create(['nama' => 'Siti Aminah', 'created_by' => $this->operator->id]);

    $response = $this->actingAs($this->operator)
        ->getJson('/api/v1/lansia?nama=Budi');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['nama'])->toBe('Budi Santoso');
});

test('list lansia supports filter by rw', function () {
    Lansia::factory()->create(['rw' => '01', 'created_by' => $this->operator->id]);
    Lansia::factory()->create(['rw' => '02', 'created_by' => $this->operator->id]);

    $response = $this->actingAs($this->operator)
        ->getJson('/api/v1/lansia?rw=01');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
});

test('operator can create lansia', function () {
    $payload = [
        'nik' => '3201234567890001',
        'nama' => 'Ahmad Wahyudi',
        'tanggal_lahir' => '1950-03-15',
        'jenis_kelamin' => 'L',
        'alamat' => 'Jl. Merdeka No. 1',
        'rt' => '01',
        'rw' => '03',
    ];

    $response = $this->actingAs($this->operator)
        ->postJson('/api/v1/lansia', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.nama', 'Ahmad Wahyudi')
        ->assertJsonPath('data.nik', '3201234567890001');

    $this->assertDatabaseHas('lansia', ['nik' => '3201234567890001', 'created_by' => $this->operator->id]);
});

test('create lansia fails with duplicate nik', function () {
    Lansia::factory()->create(['nik' => '3201234567890002', 'created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->postJson('/api/v1/lansia', [
            'nik' => '3201234567890002',
            'nama' => 'Other Person',
            'tanggal_lahir' => '1955-01-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Test',
            'rw' => '01',
        ])
        ->assertUnprocessable();
});

test('operator can view lansia detail', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/lansia/{$lansia->lansia_id}")
        ->assertOk()
        ->assertJsonPath('data.lansia_id', $lansia->lansia_id);
});

test('operator can update lansia', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->putJson("/api/v1/lansia/{$lansia->lansia_id}", ['nama' => 'Nama Baru'])
        ->assertOk()
        ->assertJsonPath('data.nama', 'Nama Baru');
});

test('operator can delete lansia', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->deleteJson("/api/v1/lansia/{$lansia->lansia_id}")
        ->assertNoContent();

    $this->assertSoftDeleted('lansia', ['lansia_id' => $lansia->lansia_id]);
});

test('lurah cannot create lansia', function () {
    $this->actingAs($this->lurah)
        ->postJson('/api/v1/lansia', [
            'nik' => '3201234567890099',
            'nama' => 'Test',
            'tanggal_lahir' => '1950-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Test',
            'rw' => '01',
        ])
        ->assertForbidden();
});

test('unauthenticated user cannot access lansia', function () {
    $this->getJson('/api/v1/lansia')->assertUnauthorized();
});

test('operator can view status bantuan lansia', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);
    BantuanGizi::factory()->create([
        'lansia_id' => $lansia->lansia_id,
        'periode_bulan' => 4,
        'periode_tahun' => 2026,
        'status_penerima' => 'penerima',
    ]);
    BantuanGizi::factory()->create([
        'lansia_id' => $lansia->lansia_id,
        'periode_bulan' => 1,
        'periode_tahun' => 2026,
        'status_penerima' => 'tidak_penerima',
    ]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/lansia/{$lansia->lansia_id}/status-bantuan")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.status_penerima', 'penerima');
});
