<?php

use App\Livewire\Lurah\ApprovalTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('lurah can view approval page', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah/approval')->assertOk();
});

test('approval table shows penerima bantuan', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['nama' => 'Budi Approval', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create([
        'lansia_id' => $lansia->lansia_id,
        'status_penerima' => 'penerima',
        'periode_bulan' => now()->month,
        'periode_tahun' => now()->year,
    ]);

    Livewire::actingAs($lurah)
        ->test(ApprovalTable::class)
        ->assertSeeText('Budi Approval');
});

test('lurah can approve individual bantuan', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    $bantuan = BantuanGizi::factory()->create([
        'lansia_id' => $lansia->lansia_id,
        'status_penerima' => 'penerima',
        'approved_at' => null,
    ]);

    Livewire::actingAs($lurah)
        ->test(ApprovalTable::class)
        ->call('approve', $bantuan->bantuan_id);

    $this->assertDatabaseHas('bantuan_gizi', [
        'bantuan_id' => $bantuan->bantuan_id,
        'approved_by' => $lurah->id,
    ]);
    $this->assertNotNull(BantuanGizi::find($bantuan->bantuan_id)->approved_at);
});

test('lurah can approve all penerima in bulk', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $bulan = now()->month;
    $tahun = now()->year;

    $lansia1 = Lansia::factory()->create(['created_by' => $operator->id]);
    $lansia2 = Lansia::factory()->create(['created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia1->lansia_id, 'status_penerima' => 'penerima', 'periode_bulan' => $bulan, 'periode_tahun' => $tahun, 'approved_at' => null]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia2->lansia_id, 'status_penerima' => 'penerima', 'periode_bulan' => $bulan, 'periode_tahun' => $tahun, 'approved_at' => null]);

    Livewire::actingAs($lurah)
        ->test(ApprovalTable::class)
        ->call('approveAll');

    expect(BantuanGizi::where('status_penerima', 'penerima')
        ->where('periode_bulan', $bulan)
        ->where('periode_tahun', $tahun)
        ->whereNull('approved_at')
        ->count()
    )->toBe(0);
});
