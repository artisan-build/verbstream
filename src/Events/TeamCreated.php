<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\States\TeamState;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TeamCreated extends Event
{
    #[StateId(TeamState::class)]
    public ?int $team_id = null;

    public int $user_id;

    public string $name;

    public bool $personal_team;

    public function apply(TeamState $state): void
    {
        $state->name = $this->name;
        $state->user_id = $this->user_id;
        $state->personal_team = $this->personal_team;
    }

    public function handle(): Team
    {
        $user = User::findOrFail($this->user_id);

        // Authorize the action
        Gate::forUser($user)->authorize('create', Team::class);

        // Validate the input
        Validator::make(['name' => $this->name], [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        // Create the team
        $team = Team::create([
            'name' => $this->name,
            'user_id' => $this->user_id,
            'personal_team' => $this->personal_team,
        ]);

        // Create pivot record with owner role
        $user->teams()->attach($team, ['role' => 'owner']);

        // Switch to the new team if requested
        $user->switchTeam($team);

        return $team->fresh();
    }
}
