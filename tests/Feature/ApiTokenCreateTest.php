<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Events\ApiTokenCreated;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
    Config::set('verbstream.api_token_permissions', ['read', 'write', 'delete']);
});

test('user can create API token', function () {
    $user = User::factory()->create();

    $token = ApiTokenCreated::commit(
        user_id: $user->id,
        name: 'Test Token',
        abilities: ['read', 'write']
    );

    // Assert token was created
    expect($token->accessToken)->toBeInstanceOf(PersonalAccessToken::class)
        ->and($token->plainTextToken)->toBeString()
        ->and($token->accessToken->name)->toBe('Test Token')
        ->and($token->accessToken->abilities)->toBe(['read', 'write'])
        ->and($token->accessToken->tokenable_id)->toBe($user->id);

    // Assert token exists in database
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(1);
});

test('validates token name', function () {
    $user = User::factory()->create();

    expect(fn () => ApiTokenCreated::commit(
        user_id: $user->id,
        name: '',
        abilities: ['read']
    ))->toThrow(ValidationException::class);

    // Assert no token was created
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(0);
});

test('validates abilities are required', function () {
    $user = User::factory()->create();

    expect(fn () => ApiTokenCreated::commit(
        user_id: $user->id,
        name: 'Test Token',
        abilities: []
    ))->toThrow(ValidationException::class);

    // Assert no token was created
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(0);
});

test('validates abilities are valid', function () {
    $user = User::factory()->create();

    expect(fn () => ApiTokenCreated::commit(
        user_id: $user->id,
        name: 'Test Token',
        abilities: ['invalid-ability']
    ))->toThrow(RuntimeException::class, 'Invalid token abilities provided.');

    // Assert no token was created
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(0);
}); 