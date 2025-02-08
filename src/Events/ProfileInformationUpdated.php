<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ProfileInformationUpdated extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $name;
    public string $email;

    public function apply(UserState $state): void
    {
        $state->name = $this->name;
        $state->email = $this->email;
    }

    public function handle(): User
    {
        $user = User::findOrFail($this->user_id);

        // Validate the input
        Validator::make([
            'name' => $this->name,
            'email' => $this->email,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        // Check if email is being changed
        $emailChanged = $user->email !== $this->email;

        // Update the user
        $user->forceFill([
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ])->save();

        // Send email verification if needed
        if ($emailChanged && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            EmailVerificationNotificationSent::fire(user_id: $user->id);
        }

        return $user->fresh();
    }
}
