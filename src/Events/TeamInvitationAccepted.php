<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Models\TeamInvitation;
use ArtisanBuild\Verbstream\States\TeamState;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TeamInvitationAccepted extends Event
{
    #[StateId(TeamState::class)]
    public int $team_id;

    public function __construct(
        int $team_id,
        public readonly int $invitation_id,
        public readonly int $user_id,
    ) {
        $this->team_id = $team_id;
    }

    public function apply(TeamState $state): void
    {
        // No state changes needed since team members are stored in a pivot table
    }

    public function handle(): void
    {
        $invitation = TeamInvitation::find($this->invitation_id);
        $user = User::find($this->user_id);

        if (! $invitation || ! $user) {
            throw new RuntimeException('Invalid invitation or user.');
        }

        if ($user->email !== $invitation->email) {
            throw new RuntimeException('This invitation was not sent to you.');
        }

        $team = Team::find($this->team_id);

        if (! $team) {
            throw new RuntimeException('The team no longer exists.');
        }

        if ($team->hasUser($user)) {
            // Clean up the invitation since it's no longer needed
            $invitation->delete();
            throw new RuntimeException('You are already on this team.');
        }

        // Add user to team with invited role
        $team->users()->attach($user, ['role' => $invitation->role]);

        // Set current team if user doesn't have one
        if (! $user->current_team_id) {
            $user->forceFill(['current_team_id' => $team->id])->save();
        }

        // Delete the invitation
        $invitation->delete();
    }
}
