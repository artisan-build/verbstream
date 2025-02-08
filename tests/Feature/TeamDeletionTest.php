<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamDeleted;
use Illuminate\Auth\Access\AuthorizationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('it deletes a team and cleans up relationships', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    // Set up team relationships
    $member->teams()->attach($team, ['role' => 'member']);
    $member->forceFill(['current_team_id' => $team->id])->save();
    $owner->forceFill(['current_team_id' => $team->id])->save();

    TeamDeleted::fire(
        team_id: $team->id,
        user_id: $owner->id
    );

    // Assert team was deleted
    expect(Team::find($team->id))->toBeNull();

    // Assert current_team_id was cleared for all users
    expect($owner->fresh()->current_team_id)->toBeNull()
        ->and($member->fresh()->current_team_id)->toBeNull();

    // Assert team-user relationships were removed
    expect($owner->teams)->toHaveCount(0)
        ->and($member->teams)->toHaveCount(0);
});

test('it prevents unauthorized users from deleting teams', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamDeleted::fire(
        team_id: $team->id,
        user_id: $otherUser->id
    ))->toThrow(AuthorizationException::class);

    // Assert team was not deleted
    expect(Team::find($team->id))->not->toBeNull();
});
