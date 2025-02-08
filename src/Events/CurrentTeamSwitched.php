<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use App\States\UserState;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class CurrentTeamSwitched extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public int $team_id;

    public function apply(UserState $state): void
    {
        $state->current_team_id = $this->team_id;
    }

    public function handle(): User
    {
        $user = User::findOrFail($this->user_id);
        $team = Team::findOrFail($this->team_id);

        // Verify the user belongs to the team
        if (! $team->hasUser($user)) {
            throw new HttpException(403, 'This action is unauthorized.');
        }

        // Update the user's current team
        $user->forceFill(['current_team_id' => $team->id])->save();

        return $user->fresh();
    }
}
