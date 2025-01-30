<?php

namespace ArtisanBuild\Verbstream\Http\Livewire;

use ArtisanBuild\Verbstream\Contracts\CreatesTeams;
use ArtisanBuild\Verbstream\Traits\RedirectsActions;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class CreateTeamForm extends Component
{
    use RedirectsActions;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    /**
     * Create a new team.
     *
     * @return mixed
     */
    public function createTeam(CreatesTeams $creator)
    {
        $this->resetErrorBag();

        $creator->create(Auth::user(), $this->state);

        return $this->redirectPath($creator);
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render()
    {
        return view('verbstream::teams.create-team-form');
    }
}
