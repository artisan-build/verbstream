<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Attributes\Hooks\Once;
use Thunk\Verbs\Event;

class EmailVerificationNotificationSent extends Event
{
    #[StateId(UserState::class)]
    public ?int $user_id = null;

    public function apply(UserState $state): void
    {
        // No state changes needed for this event
    }

    #[Once]
    public function handle(): void
    {
        $user = User::find($this->user_id);

        if ($user && $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->notify(new VerifyEmail);
        }
    }
}
