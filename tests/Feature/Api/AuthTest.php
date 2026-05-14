<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'testoperator@example.com',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'testoperator@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['token', 'user' => ['id', 'nama', 'role']],
        ]);
});

test('login fails with wrong password', function () {
    User::factory()->create(['email' => 'testop2@example.com', 'password' => bcrypt('secret')]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'testop2@example.com',
        'password' => 'wrong',
    ])->assertUnprocessable();
});

test('login fails for inactive user', function () {
    User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => bcrypt('password'),
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'inactive@example.com',
        'password' => 'password',
    ])->assertUnprocessable();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create(['email' => 'testlogout@example.com']);

    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();
});

test('logout requires authentication', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});
