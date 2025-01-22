<?php

namespace ArtisanBuild\Verbstream\Http\Controllers;

use ArtisanBuild\Verbstream\Verbstream;
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
        $team = Verbstream::newTeamModel()->findOrFail($request->team_id);

        if (! $request->user()->switchTeam($team)) {
            abort(403);
        }

        return redirect(config('fortify.home'), 303);
    }
}
