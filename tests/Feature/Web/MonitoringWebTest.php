<?php

use App\Livewire\Admin\MonitoringTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view monitoring page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/monitoring')->assertOk();
});

test('monitoring shows operator input data', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create(['name' => 'Kader RW 01']);
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    Pendataan::factory()->create(['lansia_id' => $lansia->lansia_id, 'user_id' => $operator->id]);

    Livewire::actingAs($admin)
        ->test(MonitoringTable::class)
        ->assertSeeText('Kader RW 01');
});

test('monitoring shows distribusi bantuan per rw', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['rw' => '01', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($admin)
        ->test(MonitoringTable::class)
        ->assertSeeText('01');
});
