<?php

use App\Livewire\Admin\LansiaForm;
use App\Livewire\Admin\LansiaTable;
use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view lansia table page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/lansia')->assertOk();
});

test('lansia table shows all lansia', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory(3)->create(['nama' => 'Budi Test', 'created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->assertSeeText('Budi Test');
});

test('lansia table can filter by nama', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory()->create(['nama' => 'Ahmad Setiawan', 'created_by' => $admin->id]);
    Lansia::factory()->create(['nama' => 'Siti Rahayu', 'created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->set('search', 'Ahmad')
        ->assertSeeText('Ahmad Setiawan')
        ->assertDontSeeText('Siti Rahayu');
});

test('lansia table can filter by rw', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory()->create(['rw' => '01', 'nama' => 'Lansia RW01', 'created_by' => $admin->id]);
    Lansia::factory()->create(['rw' => '02', 'nama' => 'Lansia RW02', 'created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->set('filterRw', '01')
        ->assertSeeText('Lansia RW01')
        ->assertDontSeeText('Lansia RW02');
});

test('admin can delete lansia from table', function () {
    $admin = User::factory()->admin()->create();
    $lansia = Lansia::factory()->create(['created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->call('deleteLansia', $lansia->lansia_id)
        ->assertDispatched('lansia-deleted');

    $this->assertSoftDeleted('lansia', ['lansia_id' => $lansia->lansia_id]);
});

test('admin can verify pendataan from table', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    $pendataan = Pendataan::factory()->create(['lansia_id' => $lansia->lansia_id, 'user_id' => $operator->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->call('verifikasiLansia', $pendataan->pendataan_id, 'terverifikasi');

    $this->assertDatabaseHas('pendataan', [
        'pendataan_id' => $pendataan->pendataan_id,
        'status_verifikasi' => 'terverifikasi',
        'verified_by' => $admin->id,
    ]);
});

test('admin can edit lansia data', function () {
    $admin = User::factory()->admin()->create();
    $lansia = Lansia::factory()->create(['created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaForm::class, ['id' => $lansia->lansia_id])
        ->set('nama', 'Nama Diubah')
        ->call('simpan')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('lansia', ['lansia_id' => $lansia->lansia_id, 'nama' => 'Nama Diubah']);
});
