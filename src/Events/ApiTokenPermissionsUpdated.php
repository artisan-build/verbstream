<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ApiTokenPermissionsUpdated extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public int $token_id;

    public array $abilities;

    public function apply(UserState $state): void
    {
        // No state changes needed as tokens are stored in a separate table
    }

    public function handle(): PersonalAccessToken
    {
        $user = User::findOrFail($this->user_id);
        $token = PersonalAccessToken::findOrFail($this->token_id);

        // Ensure token belongs to user
        if ($token->tokenable_id !== $user->id) {
            throw new RuntimeException('This token does not belong to you.');
        }

        // Validate the input
        Validator::make([
            'abilities' => $this->abilities,
        ], [
            'abilities' => ['required', 'array'],
            'abilities.*' => ['required', 'string'],
        ])->validateWithBag('updateApiToken');

        // Validate abilities against available permissions
        $validAbilities = array_intersect($this->abilities, config('verbstream.api_token_permissions', []));

        if (count($validAbilities) !== count($this->abilities)) {
            throw new RuntimeException('Invalid token abilities provided.');
        }

        // Update token abilities
        $token->forceFill([
            'abilities' => $validAbilities,
        ])->save();

        return $token->fresh();
    }
}
