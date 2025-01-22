<?php

use ArtisanBuild\Verbstream\Verbstream;
use Laravel\Fortify\Features;

test('registration screen can be rendered', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200);
})->skip(fn () => ! Features::enabled(Features::registration()), 'Registration support is not enabled.');

test('registration screen cannot be rendered if support is disabled', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(404);
})->skip(fn () => Features::enabled(Features::registration()), 'Registration support is enabled.');

test('new users can register', function (): void {
    $response = $this->post('/register', [
        'name' => 'New Test User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Verbstream::hasTermsAndPrivacyPolicyFeature(),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
})->skip(fn () => ! Features::enabled(Features::registration()), 'Registration support is not enabled.');
