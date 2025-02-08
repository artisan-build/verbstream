<?php

declare(strict_types=1);

namespace ArtisanBuild\Verbstream\Events;

use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class BrowserSessionsLoggedOut extends Event
{
    #[StateId(UserState::class)]
    public int $user_id;

    public string $password;
    public ?string $current_session_id;

    public function apply(UserState $state): void
    {
        // No state changes needed as sessions are managed separately
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->user_id);

        if (! Hash::check($this->password, $user->password)) {
            throw new RuntimeException('The provided password is incorrect.');
        }

        // Delete all sessions except the current one
        if ($this->current_session_id) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $this->current_session_id)
                ->delete();
        } else {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
