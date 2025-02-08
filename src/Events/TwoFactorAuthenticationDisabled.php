<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TwoFactorAuthenticationDisabled extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public function apply(UserState $state): void
    {
        $state->two_factor_secret = null;
        $state->two_factor_recovery_codes = null;
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->user_id);

        // Ensure 2FA is enabled
        if (! $user->two_factor_secret) {
            throw new RuntimeException('Two factor authentication is not enabled.');
        }

        // Disable 2FA and clear all related data
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
