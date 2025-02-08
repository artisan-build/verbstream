<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamMemberRoleUpdated;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function (): void {
    Verbs::commitImmediately();
    Gate::define('updateTeamMember', fn (User $user, Team $team) => $team->user_id === $user->id);
});

test('team owner can update member role', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $team->users()->attach($member, ['role' => 'member']);

    TeamMemberRoleUpdated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $member->email,
        role: 'admin'
    );

    expect($team->users()->where('user_id', $member->id)->first()->membership->role)
        ->toBe('admin');
});

test('non-owner cannot update member roles', function (): void {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $team->users()->attach($member, ['role' => 'member']);

    expect(fn () => TeamMemberRoleUpdated::fire(
        team_id: $team->id,
        user_id: $nonOwner->id,
        email: $member->email,
        role: 'admin'
    ))->toThrow(AuthorizationException::class);

    // Assert role was not changed
    expect($team->users()->where('user_id', $member->id)->first()->membership->role)
        ->toBe('member');
});

test('cannot update role of non-existent user', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamMemberRoleUpdated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: 'nonexistent@example.com',
        role: 'admin'
    ))->toThrow(\RuntimeException::class, 'User not found.');
});

test('cannot update role of user not on team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $nonMember = User::factory()->create();

    expect(fn () => TeamMemberRoleUpdated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $nonMember->email,
        role: 'admin'
    ))->toThrow(\RuntimeException::class, 'User is not a member of the team.');
});

test('cannot update role of team owner', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamMemberRoleUpdated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $owner->email,
        role: 'member'
    ))->toThrow(\RuntimeException::class, 'Cannot change role of team owner.');
});

test('validates role input', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $team->users()->attach($member, ['role' => 'member']);

    expect(fn () => TeamMemberRoleUpdated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $member->email,
        role: 'invalid-role'
    ))->toThrow(ValidationException::class);

    // Assert role was not changed
    expect($team->users()->where('user_id', $member->id)->first()->membership->role)
        ->toBe('member');
});
