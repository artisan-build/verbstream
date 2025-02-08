<?php

declare(strict_types=1);

use App\Models\User;
use ArtisanBuild\Verbstream\Events\PasswordConfirmed;
use Illuminate\Support\Facades\Hash;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
});

test('user can confirm their password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    PasswordConfirmed::fire(
        user_id: $user->id,
        password: 'correct-password'
    );

    // Assert password was confirmed
    expect(session('auth.password_confirmed_at'))->toBeInt()
        ->and(session('auth.password_confirmed_at'))->toBeGreaterThan(time() - 5);
});

test('cannot confirm with incorrect password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    expect(fn () => PasswordConfirmed::fire(
        user_id: $user->id,
        password: 'wrong-password'
    ))->toThrow(RuntimeException::class, 'The provided password is incorrect.');

    // Assert password was not confirmed
    expect(session('auth.password_confirmed_at'))->toBeNull();
});
