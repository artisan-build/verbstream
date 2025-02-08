<?php

namespace ArtisanBuild\Verbstream\Events;

use App\Models\Team;
use App\Models\User;
use App\States\UserState;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class UserCreated extends Event
{
    #[StateId(UserState::class)]
    public ?int $user_id = null;

    public string $name;

    public string $email;

    public string $password;

    public function apply(UserState $state)
    {
        $state->email = $this->email;
        $state->last_login = Date::now();
    }

    public function handle()
    {
        return DB::transaction(function () {
            // Create the user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
            ]);

            // Create personal team
            $team = Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', (string) $user->name, 2)[0]."'s ".config('verbstream.team_label')->value,
                'personal_team' => true,
            ]);

            // Set current team and create pivot record
            $user->forceFill(['current_team_id' => $team->id])->save();
            $user->teams()->attach($team, ['role' => 'owner']);

            // Send email verification if needed
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                EmailVerificationNotificationSent::fire(user_id: $user->id);
            }

            return $user->fresh();
        });
    }
}
