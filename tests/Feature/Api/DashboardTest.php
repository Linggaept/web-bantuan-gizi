<?php

use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
    $this->operator = User::factory()->operator()->create();
});

test('admin can access dashboard stats', function () {
    Lansia::factory(5)->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/dashboard/stats')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total_lansia',
                'per_rw',
                'distribusi_usia',
                'kondisi_kesehatan',
                'total_penerima_bantuan',
            ],
        ]);
});

test('lurah can access dashboard stats', function () {
    $this->actingAs($this->lurah)
        ->getJson('/api/v1/dashboard/stats')
        ->assertOk();
});

test('operator cannot access dashboard stats', function () {
    $this->actingAs($this->operator)
        ->getJson('/api/v1/dashboard/stats')
        ->assertForbidden();
});

test('dashboard stats show correct total lansia', function () {
    Lansia::factory(7)->create(['created_by' => $this->operator->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/dashboard/stats');

    expect($response->json('data.total_lansia'))->toBe(7);
});

test('dashboard stats include per rw breakdown', function () {
    Lansia::factory(3)->create(['rw' => '01', 'created_by' => $this->operator->id]);
    Lansia::factory(2)->create(['rw' => '02', 'created_by' => $this->operator->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/dashboard/stats');

    $perRw = collect($response->json('data.per_rw'));
    expect($perRw->firstWhere('rw', '01')['total'])->toBe(3);
    expect($perRw->firstWhere('rw', '02')['total'])->toBe(2);
});
