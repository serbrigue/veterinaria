<?php

use App\Providers\RouteServiceProvider;

test('registration screen can be rendered', function () {
    $response = $this->get('/registro');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/registro', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});
