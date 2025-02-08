<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\States\TeamState;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TeamMemberRemoved extends Event
{
    #[StateId(TeamState::class)]
    public int $team_id;

    public int $user_id;

    public string $email;

    public function apply(TeamState $state): void
    {
        // No state changes needed as team members are stored in pivot table
    }

    public function handle(): void
    {
        $team = Team::findOrFail($this->team_id);
        $user = User::findOrFail($this->user_id);

        // Authorize the action
        Gate::forUser($user)->authorize('removeTeamMember', $team);

        // Validate the input
        Validator::make([
            'email' => $this->email,
        ], [
            'email' => ['required', 'email'],
        ])->validateWithBag('removeTeamMember');

        $teamMember = User::where('email', $this->email)->first();

        if (! $teamMember) {
            throw new \RuntimeException('User not found.');
        }

        if (! $team->hasUserWithEmail($this->email)) {
            throw new \RuntimeException('User is not a member of the team.');
        }

        // Cannot remove team owner
        if ($teamMember->id === $team->user_id) {
            throw new \RuntimeException('Cannot remove team owner.');
        }

        // Clear current_team_id if this was their current team
        if ($teamMember->current_team_id === $team->id) {
            $teamMember->forceFill([
                'current_team_id' => null,
            ])->save();
        }

        // Remove from team
        $team->users()->detach($teamMember->id);
    }
}
