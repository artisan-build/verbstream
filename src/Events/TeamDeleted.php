<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\States\TeamState;
use Illuminate\Support\Facades\Gate;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TeamDeleted extends Event
{
    #[StateId(TeamState::class)]
    public int $team_id;

    public int $user_id;

    public function apply(TeamState $state): void
    {
        // The state will be automatically deleted by Verbs after this event
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->user_id);
        $team = Team::findOrFail($this->team_id);

        // Authorize the action
        Gate::forUser($user)->authorize('delete', $team);

        // Clear current_team_id for all affected users
        $team->owner()->where('current_team_id', $team->id)
            ->update(['current_team_id' => null]);

        $team->users()->where('current_team_id', $team->id)
            ->update(['current_team_id' => null]);

        // Remove all team members
        $team->users()->detach();

        // Delete the team
        $team->delete();
    }
} 