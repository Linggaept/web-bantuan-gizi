<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'username' => 'testoperator',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'testoperator',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['token', 'user' => ['id', 'nama', 'role']],
        ]);
});

test('login fails with wrong password', function () {
    User::factory()->create(['username' => 'testop2', 'password' => bcrypt('secret')]);

    $this->postJson('/api/v1/auth/login', [
        'username' => 'testop2',
        'password' => 'wrong',
    ])->assertUnprocessable();
});

test('login fails for inactive user', function () {
    User::factory()->create([
        'username' => 'inactive',
        'password' => bcrypt('password'),
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'username' => 'inactive',
        'password' => 'password',
    ])->assertUnprocessable();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create(['username' => 'testlogout']);

    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();
});

test('logout requires authentication', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});
