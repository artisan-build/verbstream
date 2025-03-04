<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use App\Models\Team;
use ArtisanBuild\Verbstream\Events\TeamDeleted;
use ArtisanBuild\Verbstream\Events\TeamMemberAdded;
use ArtisanBuild\Verbstream\Events\TeamMemberRemoved;
use ArtisanBuild\Verbstream\Events\TeamMemberRoleUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    /**
     * Delete the given team.
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, Team $team)
    {
        TeamDeleted::fire(
            team_id: $team->id,
            user_id: $request->user()->id
        );

        return redirect(config('fortify.home'), 303);
    }

    /**
     * Add a new team member to a team.
     *
     * @return RedirectResponse
     */
    public function addTeamMember(Request $request, Team $team)
    {
        TeamMemberAdded::fire(
            team_id: $team->id,
            user_id: $request->user()->id,
            email: $request->email,
            role: $request->role
        );

        return back(303);
    }

    /**
     * Update the role of an existing team member.
     *
     * @return RedirectResponse
     */
    public function updateTeamMemberRole(Request $request, Team $team)
    {
        TeamMemberRoleUpdated::fire(
            team_id: $team->id,
            user_id: $request->user()->id,
            email: $request->email,
            role: $request->role
        );

        return back(303);
    }

    /**
     * Remove a team member from the team.
     *
     * @return RedirectResponse
     */
    public function removeTeamMember(Request $request, Team $team)
    {
        TeamMemberRemoved::fire(
            team_id: $team->id,
            user_id: $request->user()->id,
            email: $request->email
        );

        return back(303);
    }
}
