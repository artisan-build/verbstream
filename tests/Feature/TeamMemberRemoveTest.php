<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamMemberRemoved;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
    Gate::define('removeTeamMember', fn (User $user, Team $team) => $team->user_id === $user->id);
});

test('team owner can remove a member', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $team->users()->attach($member, ['role' => 'member']);

    TeamMemberRemoved::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $member->email
    );

    expect($team->fresh()->hasUser($member))->toBeFalse()
        ->and($team->users)->toHaveCount(0);
});

test('non-owner cannot remove team members', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $team->users()->attach($member, ['role' => 'member']);

    expect(fn () => TeamMemberRemoved::fire(
        team_id: $team->id,
        user_id: $nonOwner->id,
        email: $member->email
    ))->toThrow(AuthorizationException::class);

    // Assert member was not removed
    expect($team->fresh()->hasUser($member))->toBeTrue()
        ->and($team->users)->toHaveCount(1);
});

test('cannot remove non-existent user', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamMemberRemoved::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: 'nonexistent@example.com'
    ))->toThrow(\RuntimeException::class, 'User not found.');
});

test('cannot remove user not on team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $nonMember = User::factory()->create();

    expect(fn () => TeamMemberRemoved::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $nonMember->email
    ))->toThrow(\RuntimeException::class, 'User is not a member of the team.');
});

test('cannot remove team owner', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamMemberRemoved::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $owner->email
    ))->toThrow(\RuntimeException::class, 'Cannot remove team owner.');
});

test('clears current team when removing member from their current team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create(['current_team_id' => $team->id]);
    $team->users()->attach($member, ['role' => 'member']);

    TeamMemberRemoved::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $member->email
    );

    expect($member->fresh()->current_team_id)->toBeNull()
        ->and($team->fresh()->hasUser($member))->toBeFalse();
}); 