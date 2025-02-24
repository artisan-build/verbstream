<?php

namespace ArtisanBuild\Verbstream\Http\Livewire;

use ArtisanBuild\Verbstream\Events\EmailVerificationNotificationSent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateProfileInformationForm extends Component
{
    use WithFileUploads;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    /**
     * The new avatar for the user.
     *
     * @var mixed
     */
    public $photo;

    /**
     * Determine if the verification email was sent.
     *
     * @var bool
     */
    public $verificationLinkSent = false;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount()
    {
        $user = Auth::user();

        $this->state = array_merge([
            'email' => $user->email,
        ], $user->withoutRelations()->toArray());
    }

    /**
     * Update the user's profile information.
     *
     * @return RedirectResponse|null
     */
    public function updateProfileInformation(UpdatesUserProfileInformation $updater)
    {
        $this->resetErrorBag();

        $updater->update(
            Auth::user(),
            $this->photo
                ? array_merge($this->state, ['photo' => $this->photo])
                : $this->state
        );

        if (isset($this->photo)) {
            return redirect()->route('profile.show');
        }

        $this->dispatch('saved');

        return null;
    }

    /**
     * Delete user's profile photo.
     *
     * @return void
     */
    public function deleteProfilePhoto()
    {
        Auth::user()->deleteProfilePhoto();
    }

    /**
     * Sent the email verification.
     *
     * @return void
     */
    public function sendEmailVerification()
    {
        EmailVerificationNotificationSent::fire(user_id: Auth::id());

        $this->verificationLinkSent = true;
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
        return view('verbstream::profile.update-profile-information-form');
    }
}
