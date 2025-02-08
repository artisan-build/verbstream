<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use ArtisanBuild\Verbstream\Events\CurrentTeamSwitched;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CurrentTeamController extends Controller
{
    /**
     * Update the authenticated user's current team.
     *
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        CurrentTeamSwitched::fire(
            user_id: $request->user()->id,
            team_id: $request->team_id
        );

        return redirect(config('fortify.home'), 303);
    }
}
