<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PasswordUpdated extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $current_password;

    public string $password;

    public string $password_confirmation;

    public function apply(UserState $state): void
    {
        // Password is stored in the user record
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->user_id);

        // Validate current password
        if (! Hash::check($this->current_password, $user->password)) {
            throw new RuntimeException('The provided password does not match your current password.');
        }

        // Validate the input
        Validator::make([
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ], [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validateWithBag('updatePassword');

        // Update password
        $user->forceFill([
            'password' => Hash::make($this->password),
        ])->save();
    }
}
