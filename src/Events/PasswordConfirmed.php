<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class PasswordConfirmed extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $password;

    public function apply(UserState $state): void
    {
        // No state changes needed for password confirmation
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->user_id);

        if (! Hash::check($this->password, $user->password)) {
            throw new RuntimeException('The provided password is incorrect.');
        }

        session()->put('auth.password_confirmed_at', time());
    }
}
