<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'last_name' => 'Tester',
        'email' => 'test@example.com',
        'tenant_name' => 'Test Restaurant',
        'tenant_domain' => 'test-restaurant',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms_accepted' => true,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('auth.activation.sent', absolute: false));

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'last_name' => 'Tester',
        'email' => 'test@example.com',
        'is_active' => false,
    ]);

    $this->assertDatabaseHas('tenants', [
        'id' => 'test-restaurant',
    ]);
});
