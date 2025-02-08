<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamNameUpdated;
use Illuminate\Auth\Access\AuthorizationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
});

test('it updates a team name', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    $updatedTeam = TeamNameUpdated::commit(
        team_id: $team->id,
        user_id: $user->id,
        name: 'Updated Team Name'
    );

    // Assert team name was updated
    expect($updatedTeam)->toBeInstanceOf(Team::class)
        ->and($updatedTeam->name)->toBe('Updated Team Name')
        ->and($updatedTeam->id)->toBe($team->id)
        ->and($updatedTeam->user_id)->toBe($user->id);

    // Assert state was updated
    expect($team->fresh()->name)->toBe('Updated Team Name');
});

test('it prevents unauthorized users from updating team names', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $otherUser = User::factory()->create();

    expect(fn () => TeamNameUpdated::commit(
        team_id: $team->id,
        user_id: $otherUser->id,
        name: 'Unauthorized Update'
    ))->toThrow(AuthorizationException::class);

    // Assert team name was not changed
    expect($team->fresh()->name)->toBe($team->name);
});
