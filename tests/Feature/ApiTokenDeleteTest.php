<?php

declare(strict_types=1);

use App\Models\User;
use ArtisanBuild\Verbstream\Events\ApiTokenCreated;
use ArtisanBuild\Verbstream\Events\ApiTokenDeleted;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\PersonalAccessToken;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
    Config::set('verbstream.api_token_permissions', ['read', 'write', 'delete']);
});

test('user can delete their API token', function () {
    $user = User::factory()->create();

    // Create token
    $token = ApiTokenCreated::commit(
        user_id: $user->id,
        name: 'Test Token',
        abilities: ['read']
    );

    // Delete token
    ApiTokenDeleted::fire(
        user_id: $user->id,
        token_id: $token->accessToken->id
    );

    // Assert token was deleted
    expect(PersonalAccessToken::find($token->accessToken->id))->toBeNull();
});

test('cannot delete token belonging to another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $token = ApiTokenCreated::commit(
        user_id: $user1->id,
        name: 'Test Token',
        abilities: ['read']
    );

    expect(fn () => ApiTokenDeleted::fire(
        user_id: $user2->id,
        token_id: $token->accessToken->id
    ))->toThrow(RuntimeException::class, 'This token does not belong to you.');

    // Assert token was not deleted
    expect(PersonalAccessToken::find($token->accessToken->id))->not->toBeNull();
});

test('cannot delete non-existent token', function () {
    $user = User::factory()->create();

    expect(fn () => ApiTokenDeleted::fire(
        user_id: $user->id,
        token_id: 99999
    ))->toThrow(ModelNotFoundException::class);
});
