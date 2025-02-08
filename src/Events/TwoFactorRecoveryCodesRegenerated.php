<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Collection;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class TwoFactorRecoveryCodesRegenerated extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public function apply(UserState $state): void
    {
        // Recovery codes are stored in the user record
    }

    public function handle(): Collection
    {
        $user = User::findOrFail($this->user_id);

        // Ensure 2FA is enabled
        if (! $user->two_factor_secret) {
            throw new RuntimeException('Two factor authentication is not enabled.');
        }

        // Generate new recovery codes
        return app(GenerateNewRecoveryCodes::class)($user);
    }
}
