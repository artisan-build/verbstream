<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamCreated;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('it creates a team and sets up relationships', function () {
    $user = User::factory()->create();

    $team = TeamCreated::commit(
        user_id: $user->id,
        name: 'Test Team',
        personal_team: false
    );

    // Assert team was created
    expect($team)->toBeInstanceOf(Team::class)
        ->and($team->name)->toBe('Test Team')
        ->and($team->user_id)->toBe($user->id)
        ->and($team->personal_team)->toBeFalse();

    // Assert pivot record exists with owner role
    expect($user->teams)->toHaveCount(1)
        ->and($user->teams->first()->id)->toBe($team->id)
        ->and($user->teams->first()->membership->role)->toBe('owner');

    // Assert it's set as the current team
    expect($user->fresh()->current_team_id)->toBe($team->id);
}); 