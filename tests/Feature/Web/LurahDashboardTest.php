<?php

use App\Livewire\Lurah\Dashboard;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('lurah can view dashboard', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah')->assertOk();
});

test('dashboard shows total lansia stat', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    Lansia::factory(7)->create(['created_by' => $operator->id]);

    Livewire::actingAs($lurah)
        ->test(Dashboard::class)
        ->assertSeeText('Total Lansia');
});

test('dashboard shows total penerima bantuan', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($lurah)
        ->test(Dashboard::class)
        ->assertSeeText('Penerima Bantuan');
});

test('dashboard shows pending approval count', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima', 'approved_at' => null]);

    Livewire::actingAs($lurah)
        ->test(Dashboard::class)
        ->assertSeeText('Pending Approval');
});
