<?php

declare(strict_types=1);

use App\Models\User;
use ArtisanBuild\Verbstream\Events\ApiTokenCreated;
use ArtisanBuild\Verbstream\Events\ApiTokenPermissionsUpdated;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use RuntimeException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
    Config::set('verbstream.api_token_permissions', ['read', 'write', 'delete']);
});

test('user can update API token permissions', function () {
    $user = User::factory()->create();

    // Create initial token
    $token = ApiTokenCreated::commit(
        user_id: $user->id,
        name: 'Test Token',
        abilities: ['read']
    );

    // Update token permissions
    $updatedToken = ApiTokenPermissionsUpdated::commit(
        user_id: $user->id,
        token_id: $token->accessToken->id,
        abilities: ['read', 'write']
    );

    // Assert token was updated
    expect($updatedToken)->toBeInstanceOf(PersonalAccessToken::class)
        ->and($updatedToken->abilities)->toBe(['read', 'write'])
        ->and($updatedToken->id)->toBe($token->accessToken->id)
        ->and($updatedToken->tokenable_id)->toBe($user->id);
});

test('validates abilities are required', function () {
    $user = User::factory()->create();

    $token = ApiTokenCreated::commit(
        user_id: $user->id,
        name: 'Test Token',
        abilities: ['read']
    );

    expect(fn () => ApiTokenPermissionsUpdated::commit(
        user_id: $user->id,
        token_id: $token->accessToken->id,
        abilities: []
    ))->toThrow(ValidationException::class);

    // Assert token abilities were not changed
    expect($token->accessToken->fresh()->abilities)->toBe(['read']);
});

test('validates abilities are valid', function () {
    $user = User::factory()->create();

    $token = ApiTokenCreated::commit(
        user_id: $user->id,
        name: 'Test Token',
        abilities: ['read']
    );

    expect(fn () => ApiTokenPermissionsUpdated::commit(
        user_id: $user->id,
        token_id: $token->accessToken->id,
        abilities: ['invalid-ability']
    ))->toThrow(RuntimeException::class, 'Invalid token abilities provided.');

    // Assert token abilities were not changed
    expect($token->accessToken->fresh()->abilities)->toBe(['read']);
});

test('cannot update token belonging to another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $token = ApiTokenCreated::commit(
        user_id: $user1->id,
        name: 'Test Token',
        abilities: ['read']
    );

    expect(fn () => ApiTokenPermissionsUpdated::commit(
        user_id: $user2->id,
        token_id: $token->accessToken->id,
        abilities: ['write']
    ))->toThrow(RuntimeException::class, 'This token does not belong to you.');

    // Assert token abilities were not changed
    expect($token->accessToken->fresh()->abilities)->toBe(['read']);
});
