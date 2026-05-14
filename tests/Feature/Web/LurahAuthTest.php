<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lurah is redirected to /lurah after login', function () {
    User::factory()->lurah()->create([
        'email' => 'lurah@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', ['email' => 'lurah@test.com', 'password' => 'password'])
        ->assertRedirect('/lurah');
});

test('admin is still redirected to /dashboard after login', function () {
    User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'password'])
        ->assertRedirect('/dashboard');
});

test('lurah can view /lurah dashboard', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah')->assertOk();
});

test('admin cannot access /lurah', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/lurah')->assertForbidden();
});

test('lurah cannot access /dashboard', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/dashboard')->assertForbidden();
});

test('guest is redirected to login from /lurah', function () {
    $this->get('/lurah')->assertRedirect('/login');
});

test('lurah can logout', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});
