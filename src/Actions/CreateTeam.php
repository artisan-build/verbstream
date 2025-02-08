<?php

namespace ArtisanBuild\Verbstream\Actions;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Contracts\CreatesTeams;
use ArtisanBuild\Verbstream\Events\TeamCreated;
use Illuminate\Database\Eloquent\Model;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array<string, string>  $input
     */
    public function create(User $user, array $input): Model
    {
        return TeamCreated::commit(
            user_id: $user->id,
            name: $input['name'],
            personal_team: false
        );
    }
}
