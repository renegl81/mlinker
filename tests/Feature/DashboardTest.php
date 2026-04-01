<?php

use App\Models\Role;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    Role::query()->firstOrCreate(['name' => 'Admin']);

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});
