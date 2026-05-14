<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('admin can view login page', function () {
    $this->get('/login')->assertOk()->assertSeeText('Masuk');
});

test('admin can login with valid credentials', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'password'])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($admin);
});

test('login fails with wrong password', function () {
    User::factory()->admin()->create(['email' => 'admin@test.com', 'password' => bcrypt('secret')]);

    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('email');
});

test('operator cannot access admin dashboard', function () {
    $operator = User::factory()->operator()->create();

    $this->actingAs($operator)->get('/dashboard')->assertForbidden();
});

test('admin can logout', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});
