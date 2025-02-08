<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use App\Models\Team;
use ArtisanBuild\Verbstream\Events\TeamInvitationCreated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamInvitationController extends Controller
{
    /**
     * Create a new team invitation.
     *
     * @param  Request  $request
     * @param  Team  $team
     * @return RedirectResponse
     */
    public function store(Request $request, Team $team)
    {
        TeamInvitationCreated::fire(
            team_id: $team->id,
            user_id: $request->user()->id,
            email: $request->email,
            role: $request->role
        );

        return back(303);
    }
}
