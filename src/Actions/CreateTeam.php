<?php

namespace ArtisanBuild\Verbstream\Actions;

use App\Models\Team;
use App\Models\User;
use ArtisanBuild\Verbstream\Contracts\CreatesTeams;
use ArtisanBuild\Verbstream\Events\AddingTeam;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array<string, string>  $input
     */
    public function create(User $user, array $input): Model
    {
        Gate::forUser($user)->authorize('create', Verbstream::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        $user->switchTeam($team = $user->ownedTeams()->create([
            'name' => $input['name'],
            'personal_team' => false,
        ]));

        return $team;
    }
}
