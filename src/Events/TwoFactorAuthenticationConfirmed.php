<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\Date;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TwoFactorAuthenticationConfirmed extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $code;

    public function apply(UserState $state): void
    {
        $state->two_factor_confirmed_at = Date::now();
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->user_id);

        // Ensure 2FA is enabled but not confirmed
        if (! $user->two_factor_secret) {
            throw new RuntimeException('Two factor authentication is not enabled.');
        }

        if ($user->two_factor_confirmed_at) {
            throw new RuntimeException('Two factor authentication is already confirmed.');
        }

        // Confirm 2FA
        app(ConfirmTwoFactorAuthentication::class)(
            $user,
            $this->code
        );

        // Set confirmed timestamp
        $user->forceFill([
            'two_factor_confirmed_at' => Date::now(),
        ])->save();
    }
}
