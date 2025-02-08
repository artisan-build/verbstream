<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\States\TeamState;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TeamNameUpdated extends Event
{
    #[StateId(TeamState::class)]
    public int $team_id;

    public int $user_id;
    public string $name;

    public function apply(TeamState $state): void
    {
        $state->name = $this->name;
    }

    public function handle(): Team
    {
        $user = User::findOrFail($this->user_id);
        $team = Team::findOrFail($this->team_id);

        // Authorize the action
        Gate::forUser($user)->authorize('update', $team);

        // Validate the input
        Validator::make(['name' => $this->name], [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateTeamName');

        // Update the team name
        $team->forceFill([
            'name' => $this->name,
        ])->save();

        return $team->fresh();
    }
}
