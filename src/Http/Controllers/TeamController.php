<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Events\TeamMemberAdded;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TeamController extends Controller
{
    /**
     * Add a new team member to a team.
     *
     * @param  Request  $request
     * @param  Team  $team
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
} 