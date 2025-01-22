<?php

namespace ArtisanBuild\Verbstream\Http\Livewire;

use ArtisanBuild\Verbstream\Actions\ValidateTeamDeletion;
use ArtisanBuild\Verbstream\Contracts\DeletesTeams;
use ArtisanBuild\Verbstream\Traits\RedirectsActions;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class DeleteTeamForm extends Component
{
    use RedirectsActions;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Indicates if team deletion is being confirmed.
     *
     * @var bool
     */
    public $confirmingTeamDeletion = false;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
    }

    /**
     * Delete the team.
     *
     * @return mixed
     */
    public function deleteTeam(ValidateTeamDeletion $validator, DeletesTeams $deleter)
    {
        $validator->validate(Auth::user(), $this->team);

        $deleter->delete($this->team);

        $this->team = null;

        return $this->redirectPath($deleter);
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render()
    {
        return view('teams.delete-team-form');
    }
}
