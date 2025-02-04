<?php

use ArtisanBuild\Verbstream\Events\UserCreated;
use ArtisanBuild\Verbstream\Verbstream;
use Laravel\Fortify\Features;
use Thunk\Verbs\Facades\Verbs;

test('registration screen can be rendered', function (): void {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
})->skip(fn () => ! Features::enabled(Features::registration()), 'Registration support is not enabled.');

test('registration screen cannot be rendered if support is disabled', function (): void {
    $response = $this->get(route('register'));

    $response->assertStatus(404);
})->skip(fn () => Features::enabled(Features::registration()), 'Registration support is enabled.');

test('new users can register', function (): void {
    $this->withoutExceptionHandling();
    Verbs::fake();
    Verbs::assertNothingCommitted();
    $response = $this->post(route('register'), [
        'name' => 'New Test User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Verbstream::hasTermsAndPrivacyPolicyFeature(),
    ]);

    Verbs::assertCommitted(UserCreated::class);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
})->skip(fn () => ! Features::enabled(Features::registration()), 'Registration support is not enabled.');
