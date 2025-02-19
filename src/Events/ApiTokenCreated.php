<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use ArtisanBuild\Adverbs\Attributes\Idempotent;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\NewAccessToken;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ApiTokenCreated extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $name;

    public array $abilities;

    public function apply(UserState $state): void
    {
        // No state changes needed as tokens are stored in a separate table
    }

    public function handle(): NewAccessToken
    {
        $user = User::findOrFail($this->user_id);

        // Validate the input
        Validator::make([
            'name' => $this->name,
            'abilities' => $this->abilities,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['required', 'array'],
            'abilities.*' => ['required', 'string'],
        ])->validateWithBag('createApiToken');

        // Validate abilities against available permissions
        $validAbilities = array_intersect($this->abilities, config('verbstream.api_token_permissions', []));

        if (count($validAbilities) !== count($this->abilities)) {
            throw new RuntimeException('Invalid token abilities provided.');
        }

        // Create the token
        return $user->createToken(
            $this->name,
            $validAbilities
        );
    }
}
