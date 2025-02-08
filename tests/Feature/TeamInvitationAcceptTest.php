<?php

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Events\TeamInvitationAccepted;
use ArtisanBuild\Verbstream\Models\TeamInvitation;
use Thunk\Verbs\Facades\Verbs;

beforeEach(function () {
    Verbs::commitImmediately();
});

test('user can accept team invitation', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $invitedUser = User::factory()->create();

    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => $invitedUser->email,
        'role' => 'member',
    ]);

    TeamInvitationAccepted::fire(
        team_id: $team->id,
        invitation_id: $invitation->id,
        user_id: $invitedUser->id
    );

    // Assert user was added to team
    expect($team->fresh()->hasUser($invitedUser))->toBeTrue()
        ->and($team->users()->where('user_id', $invitedUser->id)->first()->membership->role)
        ->toBe('member');

    // Assert invitation was deleted
    expect(TeamInvitation::find($invitation->id))->toBeNull();

    // Assert current team was set (since user had no team)
    expect($invitedUser->fresh()->current_team_id)->toBe($team->id);
});

test('user cannot accept invitation meant for someone else', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $wrongUser = User::factory()->create();

    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => 'someone@example.com',
        'role' => 'member',
    ]);

    expect(fn () => TeamInvitationAccepted::fire(
        team_id: $team->id,
        invitation_id: $invitation->id,
        user_id: $wrongUser->id
    ))->toThrow(\RuntimeException::class, 'This invitation was not sent to you.');

    // Assert user was not added to team
    expect($team->fresh()->hasUser($wrongUser))->toBeFalse();

    // Assert invitation still exists
    expect(TeamInvitation::find($invitation->id))->not->toBeNull();
});

test('cannot accept invitation for non-existent team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $invitedUser = User::factory()->create();

    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => $invitedUser->email,
        'role' => 'member',
    ]);

    $invitation_id = $invitation->id;
    $team_id = $team->id;
    $team->delete();

    expect(fn () => TeamInvitationAccepted::fire(
        team_id: $team_id,
        invitation_id: $invitation_id,
        user_id: $invitedUser->id
    ))->toThrow(\RuntimeException::class, 'Invalid invitation or user.');
});

test('cannot accept invitation if already on team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $existingMember = User::factory()->create();
    $team->users()->attach($existingMember, ['role' => 'member']);

    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => $existingMember->email,
        'role' => 'admin',
    ]);

    expect(fn () => TeamInvitationAccepted::fire(
        team_id: $team->id,
        invitation_id: $invitation->id,
        user_id: $existingMember->id
    ))->toThrow(\RuntimeException::class, 'You are already on this team.');

    // Assert role was not changed
    expect($team->users()->where('user_id', $existingMember->id)->first()->membership->role)
        ->toBe('member');

    // Assert invitation was deleted
    expect(TeamInvitation::find($invitation->id))->toBeNull();
});

test('does not change current team if user already has one', function () {
    $owner = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $owner->id]);
    $team2 = Team::factory()->create(['user_id' => $owner->id]);
    $invitedUser = User::factory()->create(['current_team_id' => $team1->id]);
    $team1->users()->attach($invitedUser, ['role' => 'member']);

    $invitation = TeamInvitation::create([
        'team_id' => $team2->id,
        'email' => $invitedUser->email,
        'role' => 'member',
    ]);

    TeamInvitationAccepted::fire(
        team_id: $team2->id,
        invitation_id: $invitation->id,
        user_id: $invitedUser->id
    );

    // Assert user was added to team2
    expect($team2->fresh()->hasUser($invitedUser))->toBeTrue();

    // Assert current team is still team1
    expect($invitedUser->fresh()->current_team_id)->toBe($team1->id);
});
