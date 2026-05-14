<?php

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
    $this->operator = User::factory()->operator()->create();
});

test('admin can set kuota bantuan', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/v1/bantuan/kuota', [
            'kuota' => 50,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ])
        ->assertOk()
        ->assertJsonPath('data.kuota', 50);
});

test('admin can get kuota bantuan', function () {
    cache()->put('bantuan_kuota_5_2026', ['kuota' => 50, 'periode_bulan' => 5, 'periode_tahun' => 2026]);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/bantuan/kuota?periode_bulan=5&periode_tahun=2026')
        ->assertOk()
        ->assertJsonPath('data.kuota', 50);
});

test('admin can trigger ranking and top lansia become penerima', function () {
    cache()->put('bantuan_kuota_5_2026', ['kuota' => 2, 'periode_bulan' => 5, 'periode_tahun' => 2026]);

    $operator = User::factory()->operator()->create();

    // oldest + buruk health = highest score
    $lansiaOld = Lansia::factory()->create([
        'tanggal_lahir' => '1940-01-01',
        'created_by' => $operator->id,
    ]);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansiaOld->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansiaOld->lansia_id, 'hasil_periksa' => 'buruk']);

    // middle age + sedang health
    $lansiaMiddle = Lansia::factory()->create([
        'tanggal_lahir' => '1950-01-01',
        'created_by' => $operator->id,
    ]);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansiaMiddle->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansiaMiddle->lansia_id, 'hasil_periksa' => 'sedang']);

    // youngest + baik health = lowest score
    $lansiaYoung = Lansia::factory()->create([
        'tanggal_lahir' => '1960-01-01',
        'created_by' => $operator->id,
    ]);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansiaYoung->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansiaYoung->lansia_id, 'hasil_periksa' => 'baik']);

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/bantuan/ranking', [
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

    $response->assertOk()->assertJsonPath('data.total_penerima', 2);

    $this->assertDatabaseHas('bantuan_gizi', [
        'lansia_id' => $lansiaOld->lansia_id,
        'status_penerima' => 'penerima',
    ]);
    $this->assertDatabaseHas('bantuan_gizi', [
        'lansia_id' => $lansiaYoung->lansia_id,
        'status_penerima' => 'tidak_penerima',
    ]);
});

test('operator cannot trigger ranking', function () {
    $this->actingAs($this->operator)
        ->postJson('/api/v1/bantuan/ranking', ['periode_bulan' => 5, 'periode_tahun' => 2026])
        ->assertForbidden();
});

test('admin can list bantuan penerima', function () {
    BantuanGizi::factory(3)->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/bantuan')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('lurah can approve bantuan', function () {
    $bantuan = BantuanGizi::factory()->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->lurah)
        ->postJson("/api/v1/bantuan/{$bantuan->bantuan_id}/approve")
        ->assertOk()
        ->assertJsonPath('data.approved_by', $this->lurah->id);

    $this->assertDatabaseHas('bantuan_gizi', [
        'bantuan_id' => $bantuan->bantuan_id,
        'approved_by' => $this->lurah->id,
    ]);
});

test('operator cannot approve bantuan', function () {
    $bantuan = BantuanGizi::factory()->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->operator)
        ->postJson("/api/v1/bantuan/{$bantuan->bantuan_id}/approve")
        ->assertForbidden();
});
