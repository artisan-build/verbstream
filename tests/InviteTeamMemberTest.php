<?php

use App\Models\User;
use ArtisanBuild\Verbstream\Features;
use ArtisanBuild\Verbstream\Http\Livewire\TeamMemberManager;
use ArtisanBuild\Verbstream\Mail\TeamInvitation;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('team members can be invited to team', function (): void {
    Mail::fake();

    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->set('addTeamMemberForm', [
            'email' => 'test@example.com',
            'role' => 'admin',
        ])->call('addTeamMember');

    Mail::assertSent(TeamInvitation::class);

    expect($user->currentTeam->fresh()->teamInvitations)->toHaveCount(1);
})->skip(fn() => ! Features::sendsTeamInvitations(), 'Team invitations not enabled.');

test('team member invitations can be cancelled', function (): void {
    Mail::fake();

    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    // Add the team member...
    $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->set('addTeamMemberForm', [
            'email' => 'test@example.com',
            'role' => 'admin',
        ])->call('addTeamMember');

    $invitationId = $user->currentTeam->fresh()->teamInvitations->first()->id;

    // Cancel the team invitation...
    $component->call('cancelTeamInvitation', $invitationId);

    expect($user->currentTeam->fresh()->teamInvitations)->toHaveCount(0);
})->skip(fn() => ! Features::sendsTeamInvitations(), 'Team invitations not enabled.');
