<?php

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamInvitationCreated;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
    Gate::define('addTeamMember', fn (User $user, Team $team) => $team->user_id === $user->id);
});

test('team owner can create invitation', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    TeamInvitationCreated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: 'newmember@example.com',
        role: 'member'
    );

    $invitation = TeamInvitation::where('team_id', $team->id)
        ->where('email', 'newmember@example.com')
        ->first();

    expect($invitation)->not->toBeNull()
        ->and($invitation->role)->toBe('member')
        ->and($invitation->team_id)->toBe($team->id)
        ->and($invitation->email)->toBe('newmember@example.com');
});

test('non-owner cannot create invitations', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamInvitationCreated::fire(
        team_id: $team->id,
        user_id: $nonOwner->id,
        email: 'newmember@example.com',
        role: 'member'
    ))->toThrow(AuthorizationException::class);

    // Assert no invitation was created
    expect(TeamInvitation::where('team_id', $team->id)->count())->toBe(0);
});

test('cannot create invitation for user already on team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $existingMember = User::factory()->create();
    $team->users()->attach($existingMember, ['role' => 'member']);

    expect(fn () => TeamInvitationCreated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: $existingMember->email,
        role: 'admin'
    ))->toThrow(\RuntimeException::class, 'User is already on the team.');

    // Assert no invitation was created
    expect(TeamInvitation::where('team_id', $team->id)->count())->toBe(0);
});

test('cannot create duplicate invitations', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    
    // Create first invitation
    TeamInvitationCreated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: 'newmember@example.com',
        role: 'member'
    );

    // Try to create duplicate invitation
    expect(fn () => TeamInvitationCreated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: 'newmember@example.com',
        role: 'admin'
    ))->toThrow(\RuntimeException::class, 'User already has a pending invitation.');

    // Assert only one invitation exists
    expect(TeamInvitation::where('team_id', $team->id)->count())->toBe(1);
});

test('validates role input', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    expect(fn () => TeamInvitationCreated::fire(
        team_id: $team->id,
        user_id: $owner->id,
        email: 'newmember@example.com',
        role: 'invalid-role'
    ))->toThrow(ValidationException::class);

    // Assert no invitation was created
    expect(TeamInvitation::where('team_id', $team->id)->count())->toBe(0);
}); 