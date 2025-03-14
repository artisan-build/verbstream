<?php

namespace ArtisanBuild\Verbstream\Actions;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Contracts\UpdatesTeamNames;
use ArtisanBuild\Verbstream\Events\TeamNameUpdated;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, Team $team, array $input): void
    {
        Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateTeamName');

        $team->forceFill([
            'name' => $input['name'],
        ])->save();

        TeamNameUpdated::commit(
            team_id: $team->id,
            user_id: $user->id,
            name: $input['name']
        );
    }
}
