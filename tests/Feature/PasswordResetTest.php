<?php

declare(strict_types=1);

use App\Models\User;
use ArtisanBuild\Verbstream\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('user can reset their password', function () {
    Event::fake([PasswordResetEvent::class]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    // Create a valid reset token
    $token = Password::createToken($user);

    PasswordReset::fire(
        email: 'test@example.com',
        password: 'new-password',
        password_confirmation: 'new-password',
        token: $token
    );

    // Assert password was reset
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue()
        ->and(Hash::check('old-password', $user->fresh()->password))->toBeFalse();

    // Assert event was dispatched
    Event::assertDispatched(PasswordResetEvent::class);
});

test('cannot reset password with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    expect(fn () => PasswordReset::fire(
        email: 'test@example.com',
        password: 'new-password',
        password_confirmation: 'new-password',
        token: 'invalid-token'
    ))->toThrow(RuntimeException::class);

    // Assert password was not changed
    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

test('validates password confirmation matches', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    expect(fn () => PasswordReset::fire(
        email: 'test@example.com',
        password: 'new-password',
        password_confirmation: 'different-password',
        token: $token
    ))->toThrow(ValidationException::class);

    // Assert password was not changed
    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

test('validates password minimum length', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    expect(fn () => PasswordReset::fire(
        email: 'test@example.com',
        password: 'short',
        password_confirmation: 'short',
        token: $token
    ))->toThrow(ValidationException::class);

    // Assert password was not changed
    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});
