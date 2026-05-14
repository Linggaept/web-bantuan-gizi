<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view dashboard page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard')->assertOk();
});

test('dashboard shows correct total lansia', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory(5)->create(['created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSee('5');
});

test('dashboard shows stat cards', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSeeText('Total Lansia');
});

test('unauthenticated cannot view dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});
