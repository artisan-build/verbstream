<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\States\TeamState;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TeamInvitationCreated extends Event
{
    #[StateId(TeamState::class)]
    public int $team_id;

    public int $user_id;

    public string $email;

    public string $role;

    public function apply(TeamState $state): void
    {
        // No state changes needed as invitations are stored in a separate table
    }

    public function handle(): void
    {
        $team = Team::findOrFail($this->team_id);
        $user = User::findOrFail($this->user_id);

        // Authorize the action
        Gate::forUser($user)->authorize('addTeamMember', $team);

        // Validate the input
        Validator::make([
            'email' => $this->email,
            'role' => $this->role,
        ], [
            'email' => ['required', 'email'],
            'role' => ['required', 'string', 'in:admin,editor,member'],
        ])->validateWithBag('createTeamInvitation');

        // Check if user is already on the team
        if ($team->hasUserWithEmail($this->email)) {
            throw new \RuntimeException('User is already on the team.');
        }

        // Check if there's already a pending invitation
        if ($team->teamInvitations()->where('email', $this->email)->exists()) {
            throw new \RuntimeException('User already has a pending invitation.');
        }

        // Create the invitation
        $invitationModel = Verbstream::teamInvitationModel();
        $invitationModel::create([
            'team_id' => $this->team_id,
            'email' => $this->email,
            'role' => $this->role,
        ]);
    }
}
