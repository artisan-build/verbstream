<?php

namespace ArtisanBuild\Verbstream\Http\Controllers\Livewire;

use ArtisanBuild\Verbstream\Verbstream;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeamController extends Controller
{
    /**
     * Show the team management screen.
     *
     * @param  int  $teamId
     * @return View
     */
    public function show(Request $request, $teamId)
    {
        $team = Verbstream::newTeamModel()->findOrFail($teamId);

        if (Gate::denies('view', $team)) {
            abort(403);
        }

        return view('teams.show', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    /**
     * Show the team creation screen.
     *
     * @return View
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Verbstream::newTeamModel());

        return view('teams.create', [
            'user' => $request->user(),
        ]);
    }
}
