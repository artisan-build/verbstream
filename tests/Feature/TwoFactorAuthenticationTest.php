<?php

declare(strict_types=1);

use App\Models\User;
use ArtisanBuild\Verbstream\Events\TwoFactorAuthenticationConfirmed;
use ArtisanBuild\Verbstream\Events\TwoFactorAuthenticationDisabled;
use ArtisanBuild\Verbstream\Events\TwoFactorAuthenticationEnabled;
use ArtisanBuild\Verbstream\Events\TwoFactorRecoveryCodesRegenerated;
use Illuminate\Support\Collection;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
});

test('user can enable two factor authentication', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $secret = 'test-secret';
    $codes = TwoFactorAuthenticationEnabled::commit(
        user_id: $user->id,
        secret: $secret
    );

    // Assert 2FA was enabled
    expect($user->fresh()->two_factor_secret)->toBe($secret)
        ->and($user->fresh()->two_factor_confirmed_at)->toBeNull()
        ->and($codes)->toBeInstanceOf(Collection::class);

    // Assert recovery codes were generated
});

test('cannot enable two factor authentication when already enabled', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'existing-secret',
        'two_factor_confirmed_at' => now(),
    ]);

    expect(fn () => TwoFactorAuthenticationEnabled::commit(
        user_id: $user->id,
        secret: 'new-secret'
    ))->toThrow(RuntimeException::class, 'Two factor authentication is already enabled.')
        ->and($user->fresh()->two_factor_secret)->toBe('existing-secret')
        ->and($user->fresh()->two_factor_confirmed_at)->not->toBeNull();

    // Assert 2FA settings were not changed
});

test('user can confirm two factor authentication', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'test-secret',
        'two_factor_confirmed_at' => null,
    ]);

    // Mock the confirmation action
    $mock = Mockery::mock(ConfirmTwoFactorAuthentication::class);
    $mock->shouldReceive('__invoke')
        ->once()
        ->with(Mockery::type(User::class), '123456')
        ->andReturnNull();
    app()->instance(ConfirmTwoFactorAuthentication::class, $mock);

    TwoFactorAuthenticationConfirmed::fire(
        user_id: $user->id,
        code: '123456'
    );

    // Assert 2FA was confirmed
    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('cannot confirm two factor authentication when not enabled', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    expect(fn () => TwoFactorAuthenticationConfirmed::fire(
        user_id: $user->id,
        code: '123456'
    ))->toThrow(RuntimeException::class, 'Two factor authentication is not enabled.');
});

test('cannot confirm two factor authentication when already confirmed', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'test-secret',
        'two_factor_confirmed_at' => now(),
    ]);

    expect(fn () => TwoFactorAuthenticationConfirmed::fire(
        user_id: $user->id,
        code: '123456'
    ))->toThrow(RuntimeException::class, 'Two factor authentication is already confirmed.');
});

test('user can disable two factor authentication', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'test-secret',
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
    ]);

    TwoFactorAuthenticationDisabled::fire(
        user_id: $user->id
    );

    // Assert 2FA was disabled and all data was cleared
    $user = $user->fresh();
    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull();
});

test('cannot disable two factor authentication when not enabled', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    expect(fn () => TwoFactorAuthenticationDisabled::fire(
        user_id: $user->id
    ))->toThrow(RuntimeException::class, 'Two factor authentication is not enabled.');
});

test('user can regenerate recovery codes', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'test-secret',
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => json_encode(['old-code-1', 'old-code-2']),
    ]);

    $regeneratedCodes = TwoFactorRecoveryCodesRegenerated::commit(
        user_id: $user->id
    );

    // Assert new codes were generated
    expect($regeneratedCodes)->toBeInstanceOf(Collection::class);
});

test('cannot regenerate recovery codes when 2FA is not enabled', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    expect(fn () => TwoFactorRecoveryCodesRegenerated::commit(
        user_id: $user->id
    ))->toThrow(RuntimeException::class, 'Two factor authentication is not enabled.');
});
