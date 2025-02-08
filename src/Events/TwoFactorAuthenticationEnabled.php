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

class TwoFactorAuthenticationEnabled extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $secret;

    public function handle(): Collection
    {
        $user = User::findOrFail($this->user_id);

        // Ensure 2FA is not already enabled
        if ($user->two_factor_secret) {
            throw new RuntimeException('Two factor authentication is already enabled.');
        }

        // Enable 2FA and store secret
        $user->forceFill([
            'two_factor_secret' => $this->secret,
        ])->save();

        // Generate recovery codes
        app(GenerateNewRecoveryCodes::class)($user);

        $user->refresh();

        return collect(json_decode((string) decrypt($user->two_factor_recovery_codes)));
    }
}
