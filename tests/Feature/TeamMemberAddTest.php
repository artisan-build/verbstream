<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamMemberAdded;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
    Gate::define('addTeamMember', fn (User $user, Team $team) => $team->user_id === $user->id);
});

test('team owner can add a new member', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $newMember = User::factory()->create();

    TeamMemberAdded::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $newMember->email,
        role: 'member'
    );

    expect($team->fresh()->hasUser($newMember))->toBeTrue()
        ->and($team->users()->where('user_id', $newMember->id)->first()->membership->role)
        ->toBe('member');
});

test('non-owner cannot add team members', function (): void {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $newMember = User::factory()->create();

    expect(fn () => TeamMemberAdded::fire(
        team_id: $team->id,
        user_id: $nonOwner->id,
        email: $newMember->email,
        role: 'member'
    ))->toThrow(AuthorizationException::class);
});

test('cannot add user that does not exist', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamMemberAdded::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: 'nonexistent@example.com',
        role: 'member'
    ))->toThrow(\RuntimeException::class, 'User not found.');
});

test('cannot add user that is already on the team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $existingMember = User::factory()->create();
    $team->users()->attach($existingMember, ['role' => 'member']);

    expect(fn () => TeamMemberAdded::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $existingMember->email,
        role: 'member'
    ))->toThrow(\RuntimeException::class, 'User is already on the team.');
});

test('validates role input', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $newMember = User::factory()->create();

    expect(fn () => TeamMemberAdded::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $newMember->email,
        role: 'invalid-role'
    ))->toThrow(ValidationException::class);
});
