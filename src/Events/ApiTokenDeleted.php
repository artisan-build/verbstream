<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Laravel\Sanctum\PersonalAccessToken;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ApiTokenDeleted extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public int $token_id;

    public function apply(UserState $state): void
    {
        // No state changes needed as tokens are stored in a separate table
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->user_id);
        $token = PersonalAccessToken::findOrFail($this->token_id);

        // Ensure token belongs to user
        if ($token->tokenable_id !== $user->id) {
            throw new RuntimeException('This token does not belong to you.');
        }

        // Delete the token
        $token->delete();
    }
}
