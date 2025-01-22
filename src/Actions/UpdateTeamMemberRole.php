<?php

namespace ArtisanBuild\Verbstream\Actions;

use ArtisanBuild\Verbstream\Events\TeamMemberUpdated;
use ArtisanBuild\Verbstream\Rules\Role;
use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateTeamMemberRole
{
    /**
     * Update the role for the given team member.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  int  $teamMemberId
     * @return void
     */
    public function update($user, $team, $teamMemberId, string $role)
    {
        Gate::forUser($user)->authorize('updateTeamMember', $team);

        Validator::make([
            'role' => $role,
        ], [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $team->users()->updateExistingPivot($teamMemberId, [
            'role' => $role,
        ]);

        TeamMemberUpdated::dispatch($team->fresh(), Verbstream::findUserByIdOrFail($teamMemberId));
    }
}
