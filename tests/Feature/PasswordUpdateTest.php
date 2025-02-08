<?php

declare(strict_types=1);

use App\Models\User;
use ArtisanBuild\Verbstream\Events\PasswordUpdated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('user can update their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    PasswordUpdated::fire(
        user_id: $user->id,
        current_password: 'current-password',
        password: 'new-password',
        password_confirmation: 'new-password'
    );

    // Assert password was updated
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue()
        ->and(Hash::check('current-password', $user->fresh()->password))->toBeFalse();
});

test('cannot update password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    expect(fn () => PasswordUpdated::fire(
        user_id: $user->id,
        current_password: 'wrong-password',
        password: 'new-password',
        password_confirmation: 'new-password'
    ))->toThrow(RuntimeException::class, 'The provided password does not match your current password.');

    // Assert password was not changed
    expect(Hash::check('current-password', $user->fresh()->password))->toBeTrue();
});

test('validates password confirmation matches', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    expect(fn () => PasswordUpdated::fire(
        user_id: $user->id,
        current_password: 'current-password',
        password: 'new-password',
        password_confirmation: 'different-password'
    ))->toThrow(ValidationException::class);

    // Assert password was not changed
    expect(Hash::check('current-password', $user->fresh()->password))->toBeTrue();
});

test('validates password minimum length', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    expect(fn () => PasswordUpdated::fire(
        user_id: $user->id,
        current_password: 'current-password',
        password: 'short',
        password_confirmation: 'short'
    ))->toThrow(ValidationException::class);

    // Assert password was not changed
    expect(Hash::check('current-password', $user->fresh()->password))->toBeTrue();
});
