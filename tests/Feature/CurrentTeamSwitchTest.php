<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\CurrentTeamSwitched;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
});

test('it switches the current team for a user', function (): void {
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $user->id]);
    $team2 = Team::factory()->create(['user_id' => $user->id]);

    // Set initial current team
    $user->forceFill(['current_team_id' => $team1->id])->save();
    $user->teams()->attach([$team1->id => ['role' => 'owner'], $team2->id => ['role' => 'owner']]);

    $updatedUser = CurrentTeamSwitched::commit(
        user_id: $user->id,
        team_id: $team2->id
    );

    // Assert current team was switched
    expect($updatedUser->current_team_id)->toBe($team2->id)
        ->and($user->fresh()->current_team_id)->toBe($team2->id);
});

test('it prevents switching to a team the user does not belong to', function (): void {
    $user = User::factory()->create();
    $otherTeam = Team::factory()->create();

    expect(fn () => CurrentTeamSwitched::fire(
        user_id: $user->id,
        team_id: $otherTeam->id
    ))->toThrow(HttpException::class, 'This action is unauthorized.');
});
