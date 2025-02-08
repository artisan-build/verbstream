<?php

namespace ArtisanBuild\Verbstream\Actions;

use App\Models\Team;
use ArtisanBuild\Verbstream\Contracts\DeletesTeams;
use ArtisanBuild\Verbstream\Events\TeamDeleted;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     */
    public function delete(Team $team): void
    {
        TeamDeleted::fire(
            team_id: $team->id,
            user_id: $team->user_id
        );
    }
}
