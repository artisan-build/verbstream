<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\States\TeamState;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TeamMemberAdded extends Event
{
    #[StateId(TeamState::class)]
    public int $team_id;

    public int $user_id;

    public string $email;

    public string $role;

    public function apply(TeamState $state): void
    {
        // No state changes needed as team members are stored in pivot table
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
        ])->validateWithBag('addTeamMember');

        $newMember = User::where('email', $this->email)->first();

        if (! $newMember) {
            throw new \RuntimeException('User not found.');
        }

        if ($team->hasUserWithEmail($this->email)) {
            throw new \RuntimeException('User is already on the team.');
        }

        $team->users()->attach(
            $newMember->id,
            ['role' => $this->role]
        );
    }
}
